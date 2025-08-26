<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/logview', name: 'app_admin_logview', methods: ['GET'])]
#[IsGranted('ROLE_ADMIN')]
class LogviewController extends BaseController
{
    public function __invoke(
        #[Autowire('%kernel.project_dir%')] string $projectDir,
        KernelInterface $kernel
    ): Response {
        $filesystem = new Filesystem();
        $filename = $projectDir.'/var/log/deploy.log';

        $entries = [];
        $entry = null;
        $dateTime = null;

        try {
            if ($filesystem->exists($filename)) {
                $contents = $filesystem->readFile($filename);
                $lines = explode("\n", $contents);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) {
                        continue;
                    }

                    if (str_starts_with($line, '>>>==============')) {
                        if (is_null($entry)) {
                            $entry = '';
                        } else {
                            throw new \LogicException(
                                'Entry finished string not found'
                            );
                        }

                        continue;
                    }

                    if (str_starts_with($line, '<<<===========')) {
                        if (is_null($entry)) {
                            throw new \LogicException('Entry not started.');
                        }

                        $entries[$dateTime] = $entry;
                        $entry = null;

                        continue;
                    }

                    if ('' === $entry) {
                        //The first line contains the dateTime string
                        $dateTime = $line;
                        $entry = $line."\n";

                        continue;
                    }

                    $entry .= $line."\n";
                }
                //  dd($contents,$entries);
            } else {
            }
        } catch (IOException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        $output = new BufferedOutput();

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput(['command' => 'about']), $output);

        return $this->render('admin/logview.html.twig', [
            'project_dir' => $projectDir,
            'logEntries'  => array_reverse($entries),
        ]);
    }
}
