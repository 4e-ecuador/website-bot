<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeCssSpriteCommand extends Command
{
    protected static $defaultName = 'app:make:cssSprite';

    /**
     * @var string
     */
    private $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir.'/assets';

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

        $io->writeln('Generating sprite image and CSS...');

        $sizes = [50, 24];
        $groups = [
            'badges' => ['Badge'],
            'events' => ['Anomaly', 'EventBadge', 'UniqueBadge_AvenirShardChallenge', 'UniqueBadge_Simulacrum'],
        ];
        $blackList = [
            'Character',
            'UniqueBadge_NL',
            'Badge_PrimeChallenge',
            'Badge_OPRLive',
            'Badge_StealthOps',
            'Badge_MissionDay_',
            'Badge_Guardian_',
            'Badge_OperationClearField_',
            'Badge_IntelOps',
        ];

        $imagesPerRow = 15;
        $flags = ['-verbose'];
        foreach ($groups as $groupName => $groupItems) {
            foreach ($sizes as $size) {
                $imageWidth = $size;
                $imageHeight = $size;
                $resultImageName = 'medals_'.$groupName.'_'.$size.'.png';
                $resultImageFile = $this->rootDir.'/images/sprites/'
                    .$resultImageName;
                $resultCssFile = $this->rootDir.'/css/medals_'.$groupName.'_'
                    .$size.'.css';
                $fileNames = [];

                $cssLines = [
                    '.medal'.$size.'-'.$groupName.' {',
                    '	width: '.$imageWidth.'px;',
                    '	height: '.$imageHeight.'px;',
                    '	display: inline-block;',
                    '	background:url(../images/sprites/'.$resultImageName
                    .') no-repeat',
                    '}',
                ];

                $colCount = 0;
                $rowCount = 0;

                foreach (
                    new \DirectoryIterator(
                        $this->rootDir.'/images/badges/'.$size
                    ) as $item
                ) {
                    if ($item->isDot()) {
                        continue;
                    }

                    $d = $item->getFilename();

                    foreach ($blackList as $black) {
                        if (0 === strpos($item->getFilename(), $black)) {
                            continue 2;
                        }
                    }

                    $found = false;

                    foreach ($groupItems as $groupItem) {
                        if (0 === strpos($item->getFilename(), $groupItem)) {
                            $found = true;
                        }
                    }

                    if (!$found) {
                        continue;
                    }
                    $fileNames[] = $item->getRealPath();

                    $xPos = $colCount ? '-'.$colCount * $imageWidth.'px'
                        : '0';
                    $yPos = $rowCount ? '-'.$rowCount * $imageHeight.'px'
                        : '0';
                    $name = str_replace('.png', '', $item->getBasename());
                    $cssLines[] = sprintf(
                        '.medal'.$size.'-'.$groupName
                        .'.medal-%s {background-position: %s %s}',
                        $name, $xPos, $yPos
                    );
                    $colCount++;
                    if ($colCount >= $imagesPerRow) {
                        $colCount = 0;
                        $rowCount++;
                    }
                }

                $command = sprintf(
                    'montage %s -background none -tile %sx -geometry +0+0 %s %s',
                    implode(' ', $fileNames),
                    $imagesPerRow,
                    implode(' ', $flags),
                    $resultImageFile
                );

                $this->execCommand($command);
                file_put_contents($resultCssFile, implode("\n", $cssLines));
            }
        }

        $io->success('Finished!');

        return 0;
    }

    private function execCommand($command)
    {
        $lastLine = system($command, $status);
        if ($status) {
            // Command exited with a status != 0
            if ($lastLine) {
                throw new \RuntimeException($lastLine);
            }

            throw new \RuntimeException('An unknown error occurred');
        }

        return $lastLine;
    }
}
