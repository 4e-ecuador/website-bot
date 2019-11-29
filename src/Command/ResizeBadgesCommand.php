<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResizeBadgesCommand extends Command
{
    protected static $defaultName = 'ResizeBadges';

    /**
     * @var string
     */
    private $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir.'/assets/images/badges';

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sizes = [50, 24];

        foreach ($sizes as $size) {
            $destDir = $this->rootDir.'/'.$size;
            if (!is_dir($destDir) && !mkdir($destDir) && !is_dir($destDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $destDir));
            }
            foreach (new \DirectoryIterator($this->rootDir) as $item) {
                if ($item->isDot()) {
                    continue;
                }
                $io->writeln($item->getRealPath());
                $srcPath = $item->getRealPath();
                if (strpos($srcPath, '_'.$size.'.png')) {
                    continue;
                }
                // $destPath = str_replace('.png', '_'.$size.'.png', $srcPath);
                $destPath = $destDir.'/'.$item->getFilename();
                $command = 'convert '.$srcPath.' -resize '.$size.'x'.$size.'\> '
                    .$destPath;
                ob_start();
                system($command, $return_var);
                $output = ob_get_contents();
                ob_end_clean();

                if ($output) {
                    $io->error($output);
                }
            }
        }

        $io->success('Finished!');

        return 0;
    }
}
