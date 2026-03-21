<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use LogicException;
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
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public function __invoke(
        #[Autowire('%kernel.project_dir%')] string $projectDir
    ): Response {
        $filesystem = new Filesystem();
        $filename = $projectDir.'/var/log/deploy.log';

        $entries = [];

        try {
            if ($filesystem->exists($filename)) {
                $entries = $this->parseLogFile(
                    $filesystem->readFile($filename)
                );
            }
        } catch (IOException $ioException) {
            $this->addFlash('danger', $ioException->getMessage());
        }

        $output = new BufferedOutput();

        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput(['command' => 'about']), $output);

        return $this->render('admin/logview.html.twig', [
            'project_dir' => $projectDir,
            'logEntries'  => array_reverse($entries),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function parseLogFile(string $contents): array
    {
        $entries = [];
        $entry = null;
        $dateTime = null;

        foreach (explode("\n", $contents) as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') {
                continue;
            }

            if ($line === '0') {
                continue;
            }

            if (str_starts_with($line, '>>>==============')) {
                $entry = $this->openLogEntry($entry);

                continue;
            }

            if (str_starts_with($line, '<<<===========')) {
                $entries[$dateTime] = $this->closeLogEntry($entry);
                $entry = null;

                continue;
            }

            if ('' === $entry) {
                $dateTime = $line;
                $entry = $line."\n";

                continue;
            }

            $entry .= $line."\n";
        }

        return $entries;
    }

    private function openLogEntry(?string $entry): string
    {
        if ($entry !== null) {
            throw new LogicException('Entry finished string not found');
        }

        return '';
    }

    private function closeLogEntry(?string $entry): string
    {
        if ($entry === null) {
            throw new LogicException('Entry not started.');
        }

        return $entry;
    }
}
