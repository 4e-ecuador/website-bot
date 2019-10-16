<?php

namespace App\Service;

use App\Entity\AgentStat;

class MedalChecker
{
    private $medalLevels
        = [
            'explorer' => [
                'desc'   => 'Unique Portals Visited',
                'levels' => [100, 1000, 2000, 10000, 30000],
            ],
            'seer'     => [
                'desc'   => 'Seer Points',
                'levels' => [10, 50, 200, 500, 5000],
            ],

            'recon'           => [
                'desc'   => 'OPR Agreements',
                'levels' => [100, 750, 2500, 5000, 10000],
            ],
            'trekker'         => [
                'desc'   => 'Distance Walked',
                'levels' => [10, 100, 300, 1000, 2500],
            ],
            'builder'         => [
                'desc'   => 'Resonators Deployed',
                'levels' => [2000, 10000, 30000, 100000, 200000],
            ],
            'connector'       => [
                'desc'   => 'Links Created',
                'levels' => [50, 1000, 5000, 25000, 100000],
            ],
            'mind-controller' => [
                'desc'   => 'Control Fields Created',
                'levels' => [100, 500, 2000, 10000, 40000],
            ],
            'engineer'        => [
                'desc'   => 'Mods Deployed',
                'levels' => [150, 1500, 5000, 20000, 50000],
            ],
            'illuminator'     => [
                'desc'   => 'Mind Units Captured',
                'levels' => [5000, 50000, 250000, 1000000, 4000000],
            ],
            'recharger'       => [
                'desc'   => 'XM Recharged',
                'levels' => [100000, 1000000, 3000000, 10000000, 25000000],
            ],
            'liberator'       => [
                'desc'   => 'Portals Captured',
                'levels' => [100, 1000, 5000, 15000, 40000],
            ],
            'pioneer'         => [
                'desc'   => 'Unique Portals Captured',
                'levels' => [20, 200, 1000, 5000, 20000],
            ],
            'purifier'        => [
                'desc'   => 'Resonators Destroyed',
                'levels' => [2000, 10000, 30000, 100000, 300000],
            ],
            'specops'         => [
                'desc'   => 'Unique Missions Completed',
                'levels' => [5, 25, 100, 200, 500],
            ],
            'missionday'      => [
                'desc'   => 'Mission Day(s) Attended',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'nl-1331-meetups' => [
                'desc'   => 'NL-1331 Meetup(s) Attended',
                'levels' => [1, 5, 10, 25, 50],
            ],
            'hacker'          => [
                'desc'   => 'Hacks',
                'levels' => [2000, 10000, 30000, 100000, 200000],
            ],
            'translator'      => [
                'desc'   => 'Glyph Hack Points',
                'levels' => [200, 2000, 6000, 20000, 50000],
            ],
            'sojourner'       => [
                'desc'   => 'Longest Hacking Streak',
                'levels' => [15, 30, 60, 180, 360],
            ],
            'recruiter'       => [
                'desc'   => 'Agents successfully recruited',
                'levels' => [2, 10, 25, 50, 100],
            ],
            'recursions'      => [
                'desc'   => 'Recursions',
                'levels' => [1, 0, 0, 0, 0],
            ],
            'prime_challenge' => [
                'desc'   => 'Prime Challenges',
                'levels' => [1, 2, 3, 4, 0],
            ],
            'stealth_ops'     => [
                'desc'   => 'Stealth Ops Missions',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'opr_live'        => [
                'desc'   => 'OPR Live Events',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'ocf'             => [
                'desc'   => 'Clear Fields Events',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'intel_ops'       => [
                'desc'   => 'Intel Ops Missions',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'ifs'             => [
                'desc'   => 'First Saturday Events',
                'levels' => [1, 6, 12, 24, 36],
            ],
        ];

    private $primeHeaders
        = [
            'Time Span'                     => '',
            'Agent Name'                    => '',
            'Agent Faction'                 => '',
            'Date (yyyy-mm-dd)'             => '',
            'Time (hh:mm:ss)'               => '',
            'Lifetime AP'                   => 'ap',
            'Current AP'                    => '',
            'Unique Portals Visited'        => 'explorer',
            'Portals Discovered'            => '',
            'Seer Points'                   => 'seer',
            'XM Collected'                  => '',
            'OPR Agreements'                => 'recon',
            'Distance Walked'               => 'trekker',
            'Resonators Deployed'           => 'builder',
            'Links Created'                 => 'connector',
            'Control Fields Created'        => 'mind-controller',
            'Mind Units Captured'           => 'illuminator',
            'Longest Link Ever Created'     => '',
            'Largest Control Field'         => '',
            'XM Recharged'                  => 'recharger',
            'Portals Captured'              => 'liberator',
            'Unique Portals Captured'       => 'pioneer',
            'Mods Deployed'                 => 'engineer',
            'Resonators Destroyed'          => 'purifier',
            'Portals Neutralized'           => '',
            'Enemy Links Destroyed'         => '',
            'Enemy Fields Destroyed'        => '',
            'Max Time Portal Held'          => '',
            'Max Time Link Maintained'      => '',
            'Max Link Length x Days'        => '',
            'Max Time Field Held'           => '',
            'Largest Field MUs x Days'      => '',
            'Unique Missions Completed'     => 'specops',
            'Hacks'                         => 'hacker',
            'Glyph Hack Points'             => 'translator',
            'Longest Hacking Streak'        => 'sojourner',
            'Agents Successfully Recruited' => 'recruiter',
            'Mission Day(s) Attended'       => 'missionday',
            'NL-1331 Meetup(s) Attended'    => 'nl-1331-meetups',
            'First Saturday Events'         => 'ifs',
        ];

    private $levelNames
        = [
            1 => 'bronce',
            2 => 'silver',
            3 => 'gold',
            4 => 'platin',
            5 => 'onyx',
        ];

    public function checkLevels(AgentStat $agentStat): array
    {
        $levels = [];
        foreach ($this->medalLevels as $name => $level) {
            $methodName = $this->getGetterMethodName($name);
            if (method_exists($agentStat, $methodName)) {
                $lv = $agentStat->$methodName();

                $medalLevel = 0;

                if ($lv >= $level['levels'][4]) {
                    $medalLevel = 5;
                } elseif ($lv >= $level['levels'][3]) {
                    $medalLevel = 4;
                } elseif ($lv >= $level['levels'][2]) {
                    $medalLevel = 3;
                } elseif ($lv >= $level['levels'][1]) {
                    $medalLevel = 2;
                } elseif ($lv >= $level['levels'][0]) {
                    $medalLevel = 1;
                }

                $levels[$name] = $medalLevel;
            }
        }

        return $levels;
    }

    public function getLevelName(int $level): string
    {
        return $this->levelNames[$level] ?? '';
    }

    public function translatePrimeHeader($name): string
    {
        if (isset($this->primeHeaders[$name])) {
            return $this->primeHeaders[$name];
        }

        return '';
        //throw new \UnexpectedValueException('Unknown Ingress Prime header: '.$name);
    }

    public function getMethodName(string $vName)
    {
        return 'set'.implode('', array_map('ucfirst', explode('-', $vName)));
    }

    public function getGetterMethodName(string $vName)
    {
        return 'get'.implode('', array_map('ucfirst', explode('-', $vName)));
    }

    public function getUpgrades(
        AgentStat $previousEntry,
        AgentStat $currentEntry
    ) {
        $upgrades = [];

        $previousLevels = $this->checkLevels($previousEntry);
        $currentLevels = $this->checkLevels($currentEntry);

        foreach ($currentLevels as $name => $currentVal) {
            if (false === isset($previousLevels[$name])
                || $currentVal > $previousLevels[$name]
            ) {
                $upgrades[$name] = $currentVal;
            }
        }

        return $upgrades;
    }
}
