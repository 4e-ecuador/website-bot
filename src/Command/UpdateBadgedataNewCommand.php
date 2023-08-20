<?php

namespace App\Command;

use App\Exception\NothingHasChangedException;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use stdClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'app:update:badgedata2',
    description: 'Scrape an ingress fan site for medal images',
)]
class UpdateBadgedataNewCommand extends Command
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

    private array $skipCategories
        = [
            'Characters',
            'NL-1331',
            'Corporation Medals',
            'Festive Medals',
            'Unused/Replaced',
            'Active Giveaways',
            'Fan created - Single',
            'Fan created - Tiered',
        ];

    private array $skipBadges
        = [

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
        $this->badgeRoot = $rootDir.'/assets/images/badges2';
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
        } catch (\Exception $e) {
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

            $this->io->note($category);

            foreach ($item->image as $image) {
                $this->io->writeln($image);

                $imageName = $this->cutHash($image);

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

                $badgeInfo->code = $this->getCode($item);
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
            $this->rootDir.'/text-files/badgeinfos2.json',
            json_encode($badgeInfos, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    private function resizeBadges(): self
    {
        $this->io->write('Querying site...');

        $this->io->writeln('ok');

        return $this;
    }

    private function makeCssSprite(): self
    {
        $this->io->write('Querying site...');

        $this->io->writeln('ok');

        return $this;
    }

    private function getCode($item): string
    {
        $cat = $item->expand->category->title;

        return match ($cat) {
            'XM Anomaly' => 'Anomaly_'.$item->title,
            'Unique Medals' => 'UniqueBadge_'.$item->title,
            default => $cat,
        };
    }

    private function skipItem($item): bool
    {
        if (false === property_exists($item, 'expand')) {
            if ($this->output->isVerbose()) {
                dump($item);
            }

            return true;
        }
        $category = $item->expand->category->title;

        if (in_array($category, $this->skipCategories, true)) {
            if ($this->output->isVerbose()) {
                $this->io->warning(
                    sprintf('Category %s has been skipped', $category)
                );
            }

            return true;
        }

        if (array_key_exists($category, $this->skipBadges)
            && in_array(
                $item->title,
                $this->skipBadges[$category],
                true
            )
        ) {
            if ($this->output->isVerbose()) {
                $this->io->warning(
                    sprintf(
                        'badge %s/%s has been skipped',
                        $category,
                        $item->title
                    )
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
            if ($this->output->isVerbose()) {
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
        // cut off extension
        $fileName = substr($fileName, 0, -4);
        $fileName = substr($fileName, 0, strrpos($fileName, '_'));
        $fileName = $fileName.'.png';

        return $fileName;
    }
}
