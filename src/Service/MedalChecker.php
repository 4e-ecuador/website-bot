<?php


namespace App\Service;


class MedalChecker
{
    private $medalLevels = [
        'explorer' => [
            'desc'   => 'Unique Portals Visited',
            'levels' => [100, 1000, 2000, 10000, 30000],
        ],
        'seer'     => [
            'desc'   => 'Seer Points',
            'levels' => [10, 50, 200, 500, 5000],
        ],

        'recon'   => [
            'desc'   => 'OPR Agreements',
            'levels' => [100, 750, 2500, 5000, 10000],
        ],
        'trekker' => [
            'desc'   => 'Distance Walked',
            'levels' => [10, 100, 300, 1000, 2500],
        ],
        'builder' => [
            'desc' => 'Resonators Deployed',
            'levels' => [2000, 10000, 30000, 100000, 200000]
        ],
        'connector' => [
            'desc' => 'Links Created',
            'levels' => [50, 1000,	5000,	25000,	100000]
        ],
        'mind-controller' => [
            'desc' => 'Control Fields Created',
            'levels' => [100,	500,	2000,	10000,	40000]
        ],
        'engineer' => [
            'desc' => 'Mods Deployed',
            'levels' => [150,	1500,	5000,	20000,	50000]
        ],





        /*
            '' => [
                'desc' => '',
                'levels' => []
            ],

    */


        /*
illuminator
Mind Units Captured
5,000	50,000	250,000	1,000,000	4,000,000
recharger
XM Recharged
100,000	1,000,000	3,000,000	10,000,000	25,000,000
liberator
Portals Captured
100	1,000	5,000	15,000	40,000
pioneer
Unique Portals Captured
20	200	1,000	5,000	20,000
purifier
Resonators Destroyed
2,000	10,000	30,000	100,000	300,000
specops
Unique Missions Completed
5	25	100	200	500
missionday
Mission Day(s) Attended
1	3	6	10	20
nl-1331-meetups
NL-1331 Meetup(s) Attended
1	5	10	25	50
hacker
Hacks
2,000	10,000	30,000	100,000	200,000
translator
Glyph Hack Points
200	2,000	6,000	20,000	50,000
sojourner
Longest Hacking Streak
15	30	60	180	360
recruiter
Agents successfully recruited
2	10	25	50	100
recursions
Recursions
1	N/A	N/A	N/A	N/A
prime_challenge
Prime Challenges
1	2	3	4	N/A
stealth_ops
Stealth Ops Missions
1	3	6	10	20
opr_live
OPR Live Events
1	3	6	10	20
ocf
Clear Fields Events
1	3	6	10	20
intel_ops
Intel Ops Missions
1	3	6	10	20
ifs
First Saturday Events
1	6	12	24	36
         */
    ];

    private $primeHeaders = [
        'Time Span' => '',
        'Agent Name' => '',
        'Agent Faction' => '',
        'Date (yyyy-mm-dd)' => '',
        'Time (hh:mm:ss)' => '',
        'Lifetime AP' => 'ap',
        'Current AP' => '',
        'Unique Portals Visited' => 'explorer',
        'Portals Discovered' => '',
        'Seer Points' => 'seer',
        'XM Collected' => '',
        'OPR Agreements' => 'recon',
        'Distance Walked' => 'trekker',
        'Resonators Deployed' => 'builder',
        'Links Created' => 'connector',
        'Control Fields Created' => 'mind-controller',
        'Mind Units Captured' => '',
        'Longest Link Ever Created' => '',
        'Largest Control Field' => '',
        'XM Recharged' => '',
        'Portals Captured' => '',
        'Unique Portals Captured' => '',
        'Mods Deployed' => 'engineer',
        'Resonators Destroyed' => '',
        'Portals Neutralized' => '',
        'Enemy Links Destroyed' => '',
        'Enemy Fields Destroyed' => '',
        'Max Time Portal Held' => '',
        'Max Time Link Maintained' => '',
        'Max Link Length x Days' => '',
        'Max Time Field Held' => '',
        'Largest Field MUs x Days' => '',
        'Unique Missions Completed' => '',
        'Hacks' => '',
        'Glyph Hack Points' => '',
        'Longest Hacking Streak' => '',
        'Agents Successfully Recruited' => '',
        'Mission Day(s) Attended' => '',
        'NL-1331 Meetup(s) Attended' => '',
        'First Saturday Events' => 'ifs',
    ];

    private $levelNames = [
        1 => 'bronce',
        2 => 'silver',
        3 => 'gold',
        4 => 'platin',
        5 => 'onyx',
        ];


    public function checkLevels(array $array): array
    {
        $levels = [];
        foreach ($this->medalLevels as $name => $level) {
            if (isset($array[$name])) {

                $lv = $array[$name];

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
}
