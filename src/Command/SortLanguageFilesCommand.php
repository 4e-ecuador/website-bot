<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class SortLanguageFilesCommand extends Command
{
    protected static $defaultName = 'SortLanguageFiles';
    private string $rootDir;
    private string $locale;
    private array $locales;

    public function __construct(string $rootDir, string $locale, array $locales)
    {
        parent::__construct();
        $this->rootDir = $rootDir;
        $this->locale = $locale;
        $this->locales = $locales;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sort strings in language files');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $fileMatch = '/translations/messages+intl-icu.%s.yaml';

        $origStrings = Yaml::parseFile(
            $this->rootDir.sprintf($fileMatch, $this->locale)
        );

        $io->text(
            sprintf(
                'Def lang: "%s" found "%d" strings',
                $this->locale,
                count($origStrings)
            )
        );

        foreach ($this->locales as $locale) {
            if ($locale === $this->locale) {
                continue;
            }

            $strings = Yaml::parseFile(
                $this->rootDir.sprintf($fileMatch, $locale)
            );

            $io->text(
                sprintf(
                    'Tra Lang: "%s" found "%d" strings',
                    $locale,
                    count($strings)
                )
            );

            $newValues = [];

            foreach ($origStrings as $key => $value) {
                if (array_key_exists($key, $strings)) {
                    $newValues[$key] = $strings[$key];
                } else {
                    $newValues[$key] = '____'.$key;
                }
            }

            file_put_contents(
                $this->rootDir.sprintf($fileMatch, $locale),
                Yaml::dump($newValues)
            );
        }

        $io->success('Language files have been sorted!');

        return 0;
    }
}
