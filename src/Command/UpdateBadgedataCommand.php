<?php

namespace App\Command;

use App\Exception\NothingHasChangedException;
use DirectoryIterator;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use Exception;
use RuntimeException;
use stdClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'app:update:badgedata',
    description: 'Scrape an ingress fan site for medal images',
)]
class UpdateBadgedataCommand extends Command
{
    private InputInterface $input;

    private OutputInterface $output;

    private SymfonyStyle $io;

    private readonly string $badgeRoot;

    private readonly string $scrapeSite;

    private readonly string $assetRoot;

    /**
     * @var int[]
     */
    private array $sizes = [50, 24];

    private array $uglyDudes
        = [
            'img_0229.png'                   => 'anomaly_discoverie.png',
            'badge_paragon_onyx.png'         => 'badge_paragon_black.png',
            'chronos_basic.png'              => 'event_badge_chronos_bronze.png',
            'chronos_advanced.png'           => 'event_badge_chronos_silver.png',
            'cryptic_memories_op_bronze.png' => 'event_badge_cryptic_memories_bronze.png',
            'cryptic_memories_op_silver.png' => 'event_badge_cryptic_memories_silver.png',
        ];

    private array $skipCategories
        = [
            'Characters',
            'Characters - 2015',
            'Characters - 2016',
            'Characters - 2017',
            'Characters - 2018',
            'Characters - 2019',
            'Characters - 2020',
            'Characters - 2022',
            'Characters - Ingress X Series (2022)',
            'Characters - 2023',
            'Characters - Ingress Origins (2023)',
            'Characters - 2024',
            'NL-1331',
            'Corporation Medals',
            'Festive Medals',
            'Urban Ops',
            'Stealth Ops',
            'OPR Live',
            'Intel Ops',
            'Prime Challenge',
            'Operation Clear Field',
            'Field Test: Hexathlon',
            'Unused/Replaced',
            'Active Giveaways',
            'Fan created - Single',
            'Fan created - Tiered',
            'Unused/Replaced - Single',
        ];

    private array $pickBadges
        = [
            'Unique Medals' => [
                'Simulacrum',
                'Avenir Shard Challenge',
                'Tessellation Paragon',
                'C.O.R.E.',
                'Solstice Recharge Challenge',
                'Peace Day 2022',
                'Dual-Core',
            ],
        ];

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $rootDir
    ) {
        $this->assetRoot = $rootDir.'/assets';
        $this->badgeRoot = $rootDir.'/assets/images/badges';
        $this->scrapeSite = 'https://ingress.dedo1911.xyz/api';

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'force',
                null,
                InputOption::VALUE_OPTIONAL,
                'Force update?',
                false
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        try {
            $this->scrapeBadges()
                ->resizeBadges()
                ->makeCssSprite();
        } catch (NothingHasChangedException) {
            $this->io->writeln('');
            $this->io->success('Nothing has changed.');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->io->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->io->success('Finished!');

        return Command::SUCCESS;
    }

    /**
     * @throws NothingHasChangedException
     */
    private function scrapeBadges(): self
    {
        $this->io->write('Querying site...');

        $uri = $this->scrapeSite
            .'/collections/badges/records?expand=category&perPage=500';

        $client = HttpClient::create();
        $response = $client->request('GET', $uri);

        $result = json_decode(
            $response->getContent(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->io->writeln('ok');

        if ($result->totalItems >= $result->perPage) {
            throw new UnexpectedValueException('Soooo many items...');
        }

        $nothingHasChanged = true;
        $badgeInfos = [];

        $progressBar = new ProgressBar($this->output, $result->totalItems);
        $progressBar->start();

        foreach ($result->items as $item) {
            if ($this->skipItem($item)) {
                continue;
            }

            $category = $item->expand->category->title;

            if ($this->output->isVerbose()) {
                $this->io->note('Cat.:'.$category);
            }

            foreach ($item->image as $image) {
                if ($this->output->isVerbose()) {
                    $this->io->writeln($image);
                }

                $imageName = $this->cutHash($image);

                if (array_key_exists($imageName, $this->uglyDudes)) {
                    $imageName = $this->uglyDudes[$imageName];
                }

                $imageUrl = $item->collectionId.'/'.$item->id.'/'.$image;
                $imgPath = $this->badgeRoot.'/'.$imageName;

                if (false === file_exists($imgPath)) {
                    file_put_contents(
                        $imgPath,
                        file_get_contents(
                            $this->scrapeSite.'/files/'.$imageUrl
                        )
                    );
                    $nothingHasChanged = false;
                }

                $badgeInfo = new stdClass();

                $badgeInfo->code = str_replace('.png', '', (string) $imageName);
                $badgeInfo->title = $item->title;
                $badgeInfo->description = $item->description;

                $badgeInfos[] = $badgeInfo;
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        if ($nothingHasChanged && false === $this->input->getOption('force')) {
            throw new NothingHasChangedException();
        }

        file_put_contents(
            $this->rootDir.'/text-files/badgeinfos.json',
            json_encode($badgeInfos, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    private function resizeBadges(): self
    {
        $this->io->writeln('');
        $this->io->writeln('Resizing...');

        $progressBar = new ProgressBar($this->output);
        $progressBar->start();

        foreach ($this->sizes as $size) {
            $destDir = $this->badgeRoot.'/'.$size;
            if (!is_dir($destDir) && !mkdir($destDir) && !is_dir($destDir)) {
                throw new RuntimeException(
                    sprintf('Directory "%s" was not created', $destDir)
                );
            }

            $files = (new Finder())
                ->files()
                ->in($this->badgeRoot);

            foreach ($files as $file) {
                $srcPath = $file->getRealPath();
                $destPath = $destDir.'/'.$file->getFilename();
                if (file_exists($destPath)) {
                    continue;
                }

                $command = 'convert '.$srcPath.' -resize '.$size.'x'.$size.'\> '
                    .$destPath;
                ob_start();
                system($command);
                $result = ob_get_clean();

                if ($result) {
                    $this->io->writeln('');
                    $this->io->error($result);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
        }

        $this->io->writeln('OK');

        return $this;
    }

    private function makeCssSprite(): self
    {
        $this->io->writeln('Generating sprite image and CSS...');

        $imagesPerRow = 15;
        $flags = ['-verbose'];

        foreach ($this->sizes as $size) {
            $imageWidth = $size;
            $imageHeight = $size;
            $resultImageName = 'medals_'.$size.'.png';
            $resultImageFile = $this->assetRoot.'/images/sprites/'
                .$resultImageName;
            $resultCssFile = $this->assetRoot.'/css/medals_'.$size.'.css';
            $fileNames = [];

            $cssLines = [
                '.medal'.$size.' {',
                '	width: '.$imageWidth.'px;',
                '	height: '.$imageHeight.'px;',
                '	display: inline-block;',
                '	background:url(../images/sprites/'.$resultImageName
                .') no-repeat',
                '}',
            ];

            $colCount = 0;
            $rowCount = 0;

            $files = (new Finder())
                ->files()
                ->in($this->badgeRoot.'/'.$size)
                ->sortByName();

            foreach ($files as $file) {
                $fileNames[] = $file->getRealPath();

                $xPos = $colCount !== 0 ? '-'.$colCount * $imageWidth.'px' : '0';
                $yPos = $rowCount !== 0 ? '-'.$rowCount * $imageHeight.'px' : '0';
                $name = str_replace('.png', '', $file->getBasename());
                $cssLines[] = sprintf(
                    '.medal'.$size.'.medal-%s {background-position: %s %s}',
                    $name,
                    $xPos,
                    $yPos
                );
                ++$colCount;
                if ($colCount >= $imagesPerRow) {
                    $colCount = 0;
                    ++$rowCount;
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

        $this->io->writeln('OK');

        return $this;
    }

    private function skipItem($item): bool
    {
        if (false === property_exists($item, 'expand')) {
            if ($this->output->isVeryVerbose()) {
                dump($item);
            }

            return true;
        }

        $category = $item->expand->category->title;

        if (in_array($category, $this->skipCategories, true)) {
            if ($this->output->isVeryVerbose()) {
                $this->io->warning(
                    sprintf('Category %s has been skipped', $category)
                );
            }

            return true;
        }

        if (array_key_exists($category, $this->pickBadges)
            && false === in_array(
                $item->title,
                $this->pickBadges[$category],
                true
            )
        ) {
            if ($this->output->isVeryVerbose()) {
                $this->io->warning(
                    sprintf(
                        'badge %s/%s has not been picked',
                        $category,
                        $item->title
                    )
                );
            }

            return true;
        }

        return false;
    }

    private function cutHash(string $fileName): string
    {
        $fileName = basename($fileName, '.png');
        $fileName = substr($fileName, 0, strrpos($fileName, '_'));

        return $fileName . '.png';
    }

    private function execCommand(string $command): bool|string
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
