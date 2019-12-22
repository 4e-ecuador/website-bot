<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScrapeBadgesCommand extends Command
{
    protected static $defaultName = 'app:scrape:badges';

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $scrapeSite;

    /**
     * ScrapeBadgesCommand constructor.
     *
     * @param string $rootDir
     */
    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir.'/assets/images/badges';
        $this->scrapeSite = 'https://dedo1911.xyz/Badges';

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

        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $the_tag = 'div';
        $the_class = 'badge';

        $html = file_get_contents($this->scrapeSite);
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        foreach (
            $xpath->query(
                '//'.$the_tag.'[contains(@class,"'.$the_class.'")]/img'
            ) as $item
        ) {
            $original = $item->getAttribute('data-original');
            print $original.'... ';

            $imgPath = $this->rootDir.'/'.$original;
            if (file_exists($imgPath)) {
                print "exists\n";
            } else {
                file_put_contents(
                    $imgPath, file_get_contents(
                        $this->scrapeSite.'/'.$original
                    )
                );
                print "ok\n";
            }
        }

        $io->success('Finished!');

        return 0;
    }
}
