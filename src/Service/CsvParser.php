<?php


namespace App\Service;


class CsvParser
{
    /**
     * @var MedalChecker
     */
    private $medalChecker;

    public function __construct(MedalChecker $medalChecker)
    {
        $this->medalChecker = $medalChecker;
    }

    public function parse(string $csvString, string $type): array
    {
        switch ($type) {
            case 'prime':
                return $this->parsePrimeCsv($csvString);
                break;
            case 'agentstats':
                return $this->parseAgentStatsCsv($csvString);
            default:
                throw new \Exception('unknown CSV type');
        }

    }

    private function parsePrimeCsv(string $csvString): array
    {
        $csv = [];

        $lines = explode("\n", trim($csvString));

        foreach ($lines as $i => $line) {
            if (0 === $i) {
                $headVars = explode("\t", trim($line));
//var_dump($headVars);
//die();

                continue;
            }

            $vars = explode("\t", $line);

            $c        = [];
            $dateTime = $vars[3].' '.$vars[4];

            foreach ($headVars as $i1 => $headVar) {
                $vName = $this->medalChecker->translatePrimeHeader($headVar);

                if ($vName) {
                    $c[$vName] = $vars[$i1];
                }
            }
//            }

            $csv[$dateTime] = $c;
//            [
//                'ap' => $vars[5],
//                'explorer' => $vars[7],
//                'discoverer' => $vars[8],
////                'seer' => $vars[9],
////                'collector' => $vars[10],
//                'recon' => $vars[11],
////                'trekker' => $vars[12],
//                'builder' => $vars[13],
//                'connector' => $vars[14],
//                'mind-controller' => $vars[15],
//                'illuminator' => $vars[16],
//                'binder' => $vars[17],
//                'country-master' => $vars[18],
//                'recharger' => $vars[19],
//                'liberator' => $vars[20],
//                'pioneer' => $vars[21],
//                'engineer' => $vars[22],
//                'purifier' => $vars[23],
//                'neutralizer' => $vars[24],
//                'disruptor' => $vars[25],
//                'salvator' => $vars[26],
//                'guardian' => $vars[27],
//                'smuggler' => $vars[28],
//                'link-master' => $vars[29],
//                'controller' => $vars[30],
//                'field-master' => $vars[31],
//                'specops' => $vars[32],

//                'missionday' => $vars[37],
//                'nl-1331-meetups' => $vars[38],

//                'hacker' => $vars[33],
//                'translator' => $vars[34],
//                'sojourner' => $vars[35],
//                'recruiter' => $vars[36],
//                'recursions' => $vars[],
//                'prime_challenge' => $vars[],
//                'stealth_ops' => $vars[],
//                'opr_live' => $vars[],
//                'ocf' => $vars[],
//                'intel_ops' => $vars[],
//                'ifs' => $vars[39],
//                'Comment' => $vars[],

//            ];
        }

        if (!$csv) {
            throw new \UnexpectedValueException('Invalid CSV');
        }

        return $csv;

    }

    private function parseAgentStatsCsv(string $csvString): array
    {
        $csv = [];

        $lines = explode("\n", trim($csvString));

        foreach ($lines as $i => $line) {
            if (0 === $i) {
                $headVars = explode("\t", $line);

                continue;
            }

            $vars = explode("\t", $line);

            $c        = [];
            $dateTime = $vars[0];

            foreach ($headVars as $i1 => $headVar) {
                if ($i1 > 0) {
                    $c[$headVar] = $vars[$i1 - 1];
                }
            }

            $csv[$dateTime] = $c;
        }

        if (!$csv) {
            throw new \UnexpectedValueException('Invalid CSV');
        }

        return $csv;
    }
}
