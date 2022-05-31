<?php

namespace App\Service;

use App\Exception\InvalidCsvException;
use App\Exception\StatsNotAllException;
use UnexpectedValueException;

class CsvParser
{
    public function __construct(private readonly MedalChecker $medalChecker)
    {
    }

    /**
     * @return array<string, array<string, string>>
     * @throws InvalidCsvException
     * @throws StatsNotAllException
     */
    public function parse(string $csvString, string $type = 'prime'): array
    {
        return match ($type) {
            'prime' => $this->parsePrimeCsv($csvString),
            'agentstats' => $this->parseAgentStatsCsv($csvString),
            default => throw new UnexpectedValueException('unknown CSV type'),
        };
    }

    /**
     * @return array<string, array<string, string>>
     * @throws InvalidCsvException
     * @throws StatsNotAllException
     */
    private function parsePrimeCsv(string $csvString): array
    {
        $csv = [];
        $headVars = [];
        $sepChar = "\t";

        $lines = explode("\n", trim($csvString));

        foreach ($lines as $i => $line) {
            $sepChar = strpos($line, ',') ? ',' : $sepChar;
            if (0 === $i) {
                $headVars = explode($sepChar, trim($line));

                continue;
            }

            $vars = explode($sepChar, $line);

            if (count($vars) !== count($headVars)) {
                throw new UnexpectedValueException(
                    'CSV field count does not match!'
                );
            }

            if (false === in_array(
                    $vars[0],
                    [
                        'GESAMT',
                        'SIEMPRE',
                        'ALL TIME',
                    ],
                    true
                )
            ) {
                throw new StatsNotAllException('Prime stats not ALL');
            }

            $c = [];
            $dateTime = $vars[3].' '.$vars[4];

            foreach ($headVars as $i1 => $headVar) {
                $vName = $this->medalChecker->translatePrimeHeader($headVar);

                if ($vName) {
                    $c[$vName] = $vars[$i1];
                }
            }

            $csv[$dateTime] = $c;
        }

        if (!$csv) {
            throw new InvalidCsvException('Invalid CSV');
        }

        return $csv;
    }

    /**
     * @return array<string, array<string, string>>
     * @throws InvalidCsvException
     */
    private function parseAgentStatsCsv(string $csvString): array
    {
        $csv = [];
        $headVars = [];

        $lines = explode("\n", trim($csvString));

        foreach ($lines as $i => $line) {
            if (0 === $i) {
                $headVars = explode("\t", $line);

                continue;
            }

            $vars = explode("\t", $line);

            $c = [];
            $dateTime = $vars[0];

            foreach ($headVars as $i1 => $headVar) {
                if ($i1 > 0) {
                    $c[$headVar] = $vars[$i1 - 1];
                }
            }

            $csv[$dateTime] = $c;
        }

        if (!$csv) {
            throw new InvalidCsvException('Invalid CSV');
        }

        return $csv;
    }
}
