<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'app:sortLanguageFiles',
    description: 'Sort strings in language files'
)]
class SortLanguageFilesCommand extends Command
{
    public function __construct(
        private readonly string $rootDir,
        private readonly string $locale,
        private readonly array $locales
    ) {
        parent::__construct();
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

        return Command::SUCCESS;
    }
}
