<?php

namespace App\Service;

use Exception;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

final class CiteService
{
    private array $cites;

    public function __construct(string $rootDir)
    {
        try {
            $values = Yaml::parseFile(
                $rootDir.'/text-files/warrior-cites-es.yaml'
            );

            if (!array_key_exists('cites', $values)) {
                throw new UnexpectedValueException(
                    'Cites file must contain an array with a key \'cites\'.'
                );
            }

            $this->cites = $values['cites'];
        } catch (ParseException $exception) {
            $this->cites = [$exception->getMessage()];
        }
    }

    public function getRandomCite(): string
    {
        try {
            $i = random_int(0, count($this->cites));
        } catch (Exception $e) {
            $i = 0;
        }

        return $this->cites[$i];
    }
}
