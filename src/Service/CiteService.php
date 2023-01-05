<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

final class CiteService
{
    public function __construct(#[Autowire('%kernel.project_dir%')] private readonly string $rootDir)
    {
    }

    public function getRandomCite(): string
    {
        $cites = $this->fetchCites();

        try {
            $count = count($cites);
            $i = ($count > 2) ? random_int(0, $count - 1) : 0;
        } catch (Exception) {
            $i = 0;
        }

        return $cites[$i];
    }

    /**
     * @return array<string>
     */
    private function fetchCites(): array
    {
        static $cites;

        if ($cites) {
            return $cites;
        }

        try {
            $values = Yaml::parseFile(
                $this->rootDir.'/text-files/warrior-cites-es.yaml'
            );

            if (!array_key_exists('cites', $values)) {
                throw new UnexpectedValueException(
                    'Cites file must contain an array with a key \'cites\'.'
                );
            }

            $cites = $values['cites'];
        } catch (ParseException|UnexpectedValueException $exception) {
            $cites = [$exception->getMessage()];
        }

        return $cites;
    }
}
