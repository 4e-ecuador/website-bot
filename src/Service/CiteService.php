<?php

namespace App\Service;

use Exception;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

final class CiteService
{
    public function __construct(private string $rootDir)
    {
    }

    public function getRandomCite(): string
    {
        $cites = $this->fetchCites();

        try {
            $i = random_int(0, count($cites) - 1);
        } catch (Exception) {
            $i = 0;
        }

        return $cites[$i];
    }

    private function fetchCites()
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
