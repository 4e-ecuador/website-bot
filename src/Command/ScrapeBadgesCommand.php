<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Symfony\Component\String\u;

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
        $this->rootDir = $rootDir;
        $this->scrapeSite = 'https://dedo1911.xyz/Badges';

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Scrape medal images');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // $the_tag = 'div';
        // $the_class = 'badge';

        $html = file_get_contents($this->scrapeSite);
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//div[contains(@class,"badge")]/img') as $item)
        {
            $original = $item->getAttribute('data-original');
            print $original.'... ';

            $imgPath = $this->rootDir.'/assets/images/badges/'.$original;
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

        $badgeInfos = [];

        // $badgeContainers = $xpath->query('//div[@class="badgecontainer"]');

        foreach ($xpath->query('//div[@class="badgecontainer"]') as $badgeContainer) {
            $badgeInfo = new \stdClass();

            foreach ($badgeContainer->getElementsByTagName('img') as $element) {
                $badgeInfo->code = u($element->getAttribute('data-original'))->slice(
                    0, strlen($element->getAttribute('data-original')) - 4
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
                $temp = u($element->nodeValue)->slice(strlen($badgeInfo->title));
                $badgeInfo->description = $temp;//$element->nodeValue;
            }

            $badgeInfos[] = $badgeInfo;
        }

        file_put_contents($this->rootDir.'/text-files/badgeinfos.json', json_encode($badgeInfos));

        $io->success('Finished!');

        return 0;
    }
}
