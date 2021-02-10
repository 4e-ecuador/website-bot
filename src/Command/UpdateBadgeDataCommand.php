<?php

namespace App\Command;

use DirectoryIterator;
use DOMDocument;
use DOMXPath;
use RuntimeException;
use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Symfony\Component\String\u;

class UpdateBadgeDataCommand extends Command
{
    protected static $defaultName = 'app:update:badgedata';// Type must be defined in base class :(

    private string $badgeRoot;
    private string $scrapeSite;
    private string $assetRoot;
    private array $sizes;
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
        $this->assetRoot = $rootDir.'/assets';
        $this->badgeRoot = $rootDir.'/assets/images/badges';
        $this->scrapeSite = 'https://dedo1911.xyz/Badges';
        $this->sizes = [50, 24];

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Scrape an ingress fan site for medal images');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->title('Scrape Badges');

        $this->scrapeBadges($input, $output)
            ->resizeBadges($input, $output)
            ->makeCssSprite($input, $output);

        $io->success('Finished!');

        return 0;
    }

    private function scrapeBadges(
        InputInterface $input,
        OutputInterface $output
    ): UpdateBadgeDataCommand {
        $io = new SymfonyStyle($input, $output);

        $io->write('Querying site...');

        $html = file_get_contents($this->scrapeSite);

        $io->writeln('ok');

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//div[contains(@class,"badge")]/img') as $item)
        {
            $original = $item->getAttribute('data-original');
            $io->write($original.'... ');

            $imgPath = $this->badgeRoot.'/'.$original;
            if (file_exists($imgPath)) {
                $io->writeln('exists');
            } else {
                file_put_contents(
                    $imgPath,
                    file_get_contents(
                        $this->scrapeSite.'/'.$original
                    )
                );
                $io->writeln('ok');
            }
        }

        $badgeInfos = [];

        foreach (
            $xpath->query('//div[@class="badgecontainer"]') as $badgeContainer
        ) {
            $badgeInfo = new stdClass();

            foreach ($badgeContainer->getElementsByTagName('img') as $element) {
                $badgeInfo->code = u(
                    $element->getAttribute('data-original')
                )->slice(
                    0,
                    strlen($element->getAttribute('data-original')) - 4
                );
            }

            $elements = $badgeContainer->getElementsByTagName('h1');
            foreach ($elements as $element) {
                $badgeInfo->title = $element->nodeValue;
            }

            $elements = $badgeContainer->getElementsByTagName('span');
            foreach ($elements as $element) {
                // @TODO HTML is malformed :(
                // $badgeInfo->x = $element->nodeValue;
                // $temp = str_replace($badgeInfo->title, '', $element->nodeValue);
                $temp = u($element->nodeValue)->slice(
                    strlen($badgeInfo->title)
                );
                $badgeInfo->description = $temp;//$element->nodeValue;
            }

            $badgeInfos[] = $badgeInfo;
        }

        file_put_contents(
            $this->rootDir.'/text-files/badgeinfos.json',
            json_encode($badgeInfos)
        );

        return $this;
    }

    private function resizeBadges(
        InputInterface $input,
        OutputInterface $output
    ): UpdateBadgeDataCommand {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Resizing...');

        foreach ($this->sizes as $size) {
            $destDir = $this->badgeRoot.'/'.$size;
            if (!is_dir($destDir) && !mkdir($destDir) && !is_dir($destDir)) {
                throw new RuntimeException(
                    sprintf('Directory "%s" was not created', $destDir)
                );
            }
            foreach (new DirectoryIterator($this->badgeRoot) as $item) {
                if ($item->isDot() || $item->isDir()) {
                    continue;
                }
                $srcPath = $item->getRealPath();
                $io->writeln($item->getRealPath());
                if (strpos($srcPath, '_'.$size.'.png')) {
                    continue;
                }
                $destPath = $destDir.'/'.$item->getFilename();
                $command = 'convert '.$srcPath.' -resize '.$size.'x'.$size.'\> '
                    .$destPath;
                ob_start();
                system($command, $return_var);
                $result = ob_get_clean();

                if ($result) {
                    $io->writeln('');
                    $io->error($result);
                }
            }
        }

        $io->writeln('OK');

        return $this;
    }

    private function makeCssSprite(
        InputInterface $input,
        OutputInterface $output
    ): UpdateBadgeDataCommand {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Generating sprite image and CSS...');

        $groups = [
            'badges' => ['Badge'],
            'events' => [
                'Anomaly',
                'EventBadge',
                'UniqueBadge_AvenirShardChallenge',
                'UniqueBadge_Simulacrum',
                'UniqueBadge_CORE',
                'UniqueBadge_Paragon',
            ],
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
            foreach ($this->sizes as $size) {
                $imageWidth = $size;
                $imageHeight = $size;
                $resultImageName = 'medals_'.$groupName.'_'.$size.'.png';
                $resultImageFile = $this->assetRoot.'/images/sprites/'
                    .$resultImageName;
                $resultCssFile = $this->assetRoot.'/css/medals_'.$groupName.'_'
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
                    new DirectoryIterator(
                        $this->badgeRoot.'/'.$size
                    ) as $item
                ) {
                    if ($item->isDot()) {
                        continue;
                    }

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
                        $name,
                        $xPos,
                        $yPos
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

        $io->writeln('OK');

        return $this;
    }

    private function execCommand($command)
    {
        $lastLine = system($command, $status);
        if ($status) {
            // Command exited with a status != 0
            if ($lastLine) {
                throw new RuntimeException($lastLine);
            }

            throw new RuntimeException('An unknown error occurred');
        }

        return $lastLine;
    }
}
