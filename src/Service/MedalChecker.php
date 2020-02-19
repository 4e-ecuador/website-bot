<?php

namespace App\Service;

use App\Entity\AgentStat;
use App\Util\BadgeData;
use Symfony\Contracts\Translation\TranslatorInterface;

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
            'recon'                  => [
                'desc'   => 'OPR Agreements',
                'levels' => [100, 750, 2500, 5000, 10000],
            ],
            'trekker'                => [
                'desc'   => 'Distance Walked',
                'levels' => [10, 100, 300, 1000, 2500],
            ],
            'builder'                => [
                'desc'   => 'Resonators Deployed',
                'levels' => [2000, 10000, 30000, 100000, 200000],
            ],
            'connector'              => [
                'desc'   => 'Links Created',
                'levels' => [50, 1000, 5000, 25000, 100000],
            ],
            'mind-controller'        => [
                'desc'   => 'Control Fields Created',
                'levels' => [100, 500, 2000, 10000, 40000],
            ],
            'engineer'               => [
                'desc'   => 'Mods Deployed',
                'levels' => [150, 1500, 5000, 20000, 50000],
            ],
            'illuminator'            => [
                'desc'   => 'Mind Units Captured',
                'levels' => [5000, 50000, 250000, 1000000, 4000000],
            ],
            'recharger'              => [
                'desc'   => 'XM Recharged',
                'levels' => [100000, 1000000, 3000000, 10000000, 25000000],
            ],
            'liberator'              => [
                'desc'   => 'Portals Captured',
                'levels' => [100, 1000, 5000, 15000, 40000],
            ],
            'pioneer'                => [
                'desc'   => 'Unique Portals Captured',
                'levels' => [20, 200, 1000, 5000, 20000],
            ],
            'purifier'               => [
                'desc'   => 'Resonators Destroyed',
                'levels' => [2000, 10000, 30000, 100000, 300000],
            ],
            'specops'                => [
                'desc'   => 'Unique Missions Completed',
                'levels' => [5, 25, 100, 200, 500],
            ],
            'missionday'             => [
                'desc'   => 'Mission Day(s) Attended',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'nl-1331-meetups'        => [
                'desc'   => 'NL-1331 Meetup(s) Attended',
                'levels' => [1, 5, 10, 25, 50],
            ],
            'hacker'                 => [
                'desc'   => 'Hacks',
                'levels' => [2000, 10000, 30000, 100000, 200000],
            ],
            'translator'             => [
                'desc'   => 'Glyph Hack Points',
                'levels' => [200, 2000, 6000, 20000, 50000],
            ],
            'sojourner'              => [
                'desc'   => 'Longest Hacking Streak',
                'levels' => [15, 30, 60, 180, 360],
            ],
            'recruiter'              => [
                'desc'   => 'Agents successfully recruited',
                'levels' => [2, 10, 25, 50, 100],
            ],
            'recursions'             => [
                'desc'   => 'Recursions',
                'levels' => [1, 0, 0, 0, 0],
            ],
            'prime_challenge'        => [
                'desc'   => 'Prime Challenges',
                'levels' => [1, 2, 3, 4, 0],
            ],
            'stealth_ops'            => [
                'desc'   => 'Stealth Ops Missions',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'opr_live'               => [
                'desc'   => 'OPR Live Events',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'ocf'                    => [
                'desc'   => 'Clear Fields Events',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'intel_ops'              => [
                'desc'   => 'Intel Ops Missions',
                'levels' => [1, 3, 6, 10, 20],
            ],
            'ifs'                    => [
                'desc'   => 'First Saturday Events',
                'levels' => [1, 6, 12, 24, 36],
            ],
            'Umbra Deploy Challenge' => [
                'desc'   => 'Umbra Deploy Challenge',
                'levels' => [120, 600, 1440, null, null],
            ],
            'Didact Field Challenge' => [
                'desc'   => 'Didact Field Challenge',
                'levels' => [100, 300, 800, null, null],
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

            'Umbra: Unique Resonator Slots Deployed' => 'currentChallenge',
            'Didact Fields Created' => 'currentChallenge',
        ];

    private $customMedals
        = [
            'Anomaly' =>
                [
                    'Requiem',
                    'Umbra',
                    'NemesisMyriad',
                    'AbaddonPrime',
                    'DarsanaPrime',
                    'RecursionPrime',
                    'CassandraPrime',
                    'EXO5',
                    '13MAGNUSReawakens',
                    'ViaNoir',
                    'ViaLux',
                    'AegisNova',
                    'Obsidian',
                    'Abaddon',
                    'Persepolis',
                    'Shonin',
                    'Darsana',
                    'Helios',
                    'Initio',
                    'Interitus',
                    'Recursion',
                ],
            'Annual'  =>
                [
                    'Innovator'  => [],
                    'Vanguard'   => [],
                    'Luminary'   => [],
                    'Sage'       => [],
                    'Ouroboros'  => [],
                    'Resurgence' => [],
                ],
            'Event'   =>
                [
                    'DidactField'          =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'UmbraDeploy'          =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'AvenirShard'          =>
                        [
                            '',
                        ],
                    'AuroraGlyph'          =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'MyriadHack'           =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'DarkXM'               =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'CassandraNeutralizer' =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'EXO5'                 =>
                        [
                            '100',
                            '500',
                            '2000',
                        ],
                    'MagnusBuilder'        =>
                        [
                            'Builder',
                            'Architect',
                        ],
                    'LuxAdventure'         =>
                        [
                            'Explorer',
                            'Odyssey',
                        ],

                    // 'FieldTest'            =>
                    //     [
                    //         'XX',
                    //         'Elite',
                    //     ],
                ],

        ];

    private $discontinuedMedals = ['recruiter', 'seer'];

    private $levelNames
        = [
            1 => 'Bronze',
            2 => 'Silver',
            3 => 'Gold',
            4 => 'Platinum',
            5 => 'Black',
        ];

    private $ingressLevels
        = [
            '2' => [
                'ap'     => 2500,
                'medals' => [],
            ],
            '3' => [
                'ap'     => 20000,
                'medals' => [],
            ],
            '4' => [
                'ap'     => 70000,
                'medals' => [],
            ],
            '5' => [
                'ap'     => 150000,
                'medals' => [],
            ],
            '6' => [
                'ap'     => 300000,
                'medals' => [],
            ],
            '7' => [
                'ap'     => 600000,
                'medals' => [],
            ],
            '8' => [
                'ap'     => 1200000,
                'medals' => [],
            ],
            '9' => [
                'ap'     => 2400000,
                'medals' => [],
            ],
        ];

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $translatedLevels = [];

    /**
     * @var string
     */
    private $rootDir;

    public function __construct(TranslatorInterface $translator, string $rootDir)
    {
        $this->translator = $translator;

        $this->translatedLevels[1] = $translator->trans('medal.level.bronce');
        $this->translatedLevels[2] = $translator->trans('medal.level.silver');
        $this->translatedLevels[3] = $translator->trans('medal.level.gold');
        $this->translatedLevels[4] = $translator->trans('medal.level.platinum');
        $this->translatedLevels[5] = $translator->trans('medal.level.onyx');
        $this->rootDir = $rootDir;
    }

    public function checkLevels(AgentStat $agentStat): array
    {
        $levels = [];
        foreach ($this->medalLevels as $name => $level) {
            $methodName = $this->getGetterMethodName($name);
            if (method_exists($agentStat, $methodName)) {
                $levels[$name] = $this->getMedalLevel(
                    $name, $agentStat->$methodName() ?: 0
                );

                if (0 === $levels[$name]
                    && in_array($name, $this->discontinuedMedals, true)
                ) {
                    unset($levels[$name]);
                }
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
        return $this->primeHeaders[$name] ?? '';
        //throw new \UnexpectedValueException('Unknown Ingress Prime header: '.$name);
    }

    public function getMethodName(string $vName): string
    {
        return 'set'.implode('', array_map('ucfirst', explode('-', $vName)));
    }

    public function getGetterMethodName(string $vName): string
    {
        return 'get'.implode('', array_map('ucfirst', explode('-', $vName)));
    }

    public function getUpgrades(AgentStat $previousEntry, AgentStat $currentEntry): array
    {
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

    public function getDescription(string $medal): string
    {
        return array_key_exists($medal, $this->medalLevels)
            ? $this->medalLevels[$medal]['desc'] : '';
    }

    public function getLevelValue(string $medal, int $level): int
    {
        return
            array_key_exists($medal, $this->medalLevels)
            && array_key_exists(
                $level - 1, $this->medalLevels[$medal]['levels']
            )
                ? $this->medalLevels[$medal]['levels'][$level - 1]
                : 0;
    }

    public function getMedalLevel(string $medal, int $value): int
    {
        if ('nl1331Meetups' === $medal) {
            $medal = 'nl-1331-meetups';
        }
        if ('mindController' === $medal) {
            $medal = 'mind-controller';
        }
        if (false === array_key_exists($medal, $this->medalLevels)) {
            return 0;
        }

        $medalLevel = 0;
        $level = $this->medalLevels[$medal]['levels'];

        if (null !== $level[4] && $value >= $level[4]) {
            $medalLevel = 5;
        } elseif (null !== $level[3] && $value >= $level[3]) {
            $medalLevel = 4;
        } elseif ($value >= $level[2]) {
            $medalLevel = 3;
        } elseif ($value >= $level[1]) {
            $medalLevel = 2;
        } elseif ($value >= $level[0]) {
            $medalLevel = 1;
        }

        return $medalLevel;
    }

    public function translateMedalLevel(string $level): string
    {
        return $this->translatedLevels[$level] ?? $level;
    }

    public function getDoubleValue(string $medal, int $value): int
    {
        $doubleValue = 0;

        if (5 === $this->getMedalLevel($medal, $value)) {
            $base = $this->medalLevels[$medal]['levels'][4];

            $doubleValue = (int)($value / $base);
        }

        return $doubleValue;
    }

    public function getBadgePath(string $medal, int $level, int $size = 0, string $postFix = '.png'): string
    {
        $medal = ucfirst($medal);
        switch ($medal) {
            case 'Mind-controller':
                $medal = 'MindController';
                break;
            case 'Recon':
                $medal = 'OPR';
                break;
            case 'Specops':
                $medal = 'SpecOps';
                break;
            case 'Missionday':
                $medal = 'MissionDayPrime';
                break;
            case 'Nl-1331-meetups':
            case 'Nl1331Meetups':
                $medal = 'NL1331';
                break;
            case 'Ifs':
                $medal = 'FS';
                break;
        }

        $sizeString = $size ? '_'.$size : '';

        return 'Badge_'.$medal.'_'.$this->getLevelName($level).$sizeString
            .$postFix;
    }

    public function getChallengePath(string $medal, int $level): string
    {
        return 'EventBadge_'.$medal.'_'.$this->getLevelName($level);
    }

    public function getCustomMedalGroups(): array
    {
        return $this->customMedals;
    }

    public function getMedalLevelNames(): array
    {
        return $this->levelNames;
    }

    public function getMedalLevelName(int $level)
    {
        return array_key_exists($level, $this->levelNames)
            ? $this->levelNames[$level] : '??';
    }

    public function getBadgeData(string $code): BadgeData
    {
        static $badgeData;

        if (!$badgeData) {
            $badgeData = json_decode(
                file_get_contents(
                    $this->rootDir.'/text-files/badgeinfos.json'
                ), true
            );
        }

        foreach ($badgeData as $item) {
            if ($item['code'] === $code) {
                return new BadgeData($item);
            }
        }

        throw new \UnexpectedValueException('No data for code:'.$code);
    }
}
