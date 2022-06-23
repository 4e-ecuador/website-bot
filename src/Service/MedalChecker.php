<?php

namespace App\Service;

use App\Entity\AgentStat;
use App\Util\BadgeData;
use JsonException;
use Symfony\Contracts\Translation\TranslatorInterface;
use UnexpectedValueException;

class MedalChecker
{
    /**
     * @var array<string, array<string, array<int, int|null>|string>>
     */
    private array $medalLevels
        = [
            'explorer'               => [
                'desc'   => 'Unique Portals Visited',
                'levels' => [100, 1000, 2000, 10000, 30000],
            ],
            'seer'                   => [
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
            'epoch'                  => [
                'desc'   => 'Complete Hacking Streak',
                'levels' => [2, 4, 8, 30, 60],
            ],
            'recruiter'              => [
                'desc'   => 'Agents successfully recruited',
                'levels' => [2, 10, 25, 50, 100],
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
            'second-sunday'          => [
                'desc'   => 'Second Sunday Events',
                'levels' => [1, 6, 12, 24, 36],
            ],
            'scout'                  => [
                'desc'   => 'Scan portals',
                'levels' => [50, 250, 1000, 3000, 6000],
            ],
            'scout-controller'       => [
                'desc'   => 'Scan unique portals',
                'levels' => [100, 500, 1000, 5000, 15000],
            ],
            'maverick'               => [
                'desc'   => 'Drone hack portals',
                'levels' => [250, 1000, 2000, 5000, 10000],
            ],
            'Umbra Deploy Challenge' => [
                'desc'   => 'Umbra Deploy Challenge',
                'levels' => [120, 600, 1440, null, null],
            ],
            'Didact Field Challenge' => [
                'desc'   => 'Didact Field Challenge',
                'levels' => [100, 300, 800, null, null],
            ],
            'CSans'                  => [
                'desc'   => 'C. Sans Challenge',
                'levels' => [4, 41, 401, null, null],
            ],
        ];

    /**
     * @var array<string, string>
     */
    private array $primeHeaders
        = [
            'Time Span'                 => '',
            'Agent Name'                => 'nickname',
            'Agent Faction'             => 'faction',
            'Date (yyyy-mm-dd)'         => '',
            'Time (hh:mm:ss)'           => '',
            'Lifetime AP'               => 'ap',
            'Current AP'                => '',
            'Unique Portals Visited'    => 'explorer',
            'Portals Discovered'        => 'portals-discovered',
            'Seer Points'               => 'seer',
            'XM Collected'              => '',
            'OPR Agreements'            => 'recon',
            'Distance Walked'           => 'trekker',
            'Resonators Deployed'       => 'builder',
            'Links Created'             => 'connector',
            'Control Fields Created'    => 'mind-controller',
            'Mind Units Captured'       => 'illuminator',
            'Longest Link Ever Created' => 'longestLink',
            'Largest Control Field'     => 'largestField',
            'XM Recharged'              => 'recharger',
            'Portals Captured'          => 'liberator',
            'Unique Portals Captured'   => 'pioneer',
            'Mods Deployed'             => 'engineer',
            'Resonators Destroyed'      => 'purifier',
            'Portals Neutralized'       => '',
            'Enemy Links Destroyed'     => '',
            'Enemy Fields Destroyed'    => '',
            'Max Time Portal Held'      => '',
            'Max Time Link Maintained'  => '',
            'Max Link Length x Days'    => '',
            'Max Time Field Held'       => '',
            'Largest Field MUs x Days'  => '',
            'Unique Missions Completed' => 'specops',
            'Hacks'                     => 'hacker',
            'Glyph Hack Points'         => 'translator',
            'Longest Hacking Streak'    => 'sojourner',
            'Longest Sojourner Streak'  => 'sojourner',

            'Battle Beacon Combatant'       => '',
            'Kureze Effect'                 => '',

            // Sojourner 2.0
            'Completed Hackstreaks'         => 'epoch-hackstreaks',

            // Old
            'Agents Successfully Recruited' => 'recruiter',
            // New
            'Agents Recruited'              => 'recruiter',

            'Mission Day(s) Attended'    => 'missionday',
            'NL-1331 Meetup(s) Attended' => 'nl-1331-meetups',
            'First Saturday Events'      => 'ifs',
            'Second Sunday Events'       => 'second-sunday',

            'Portal Scans Uploaded'              => 'scout',

            // Old
            'Scout Controller on Unique Portals' => 'scout-controller',
            // New
            'Uniques Scout Controlled'           => 'scout-controller',

            'Drone Hacks'                  => 'maverick',
            'Unique Portals Drone Visited' => 'drone-portals-visited',
            'Furthest Drone Distance'      => 'drone-flight-distance',
            'Forced Drone Recalls'         => 'drone-forced-recalls',
            'Drones Returned'              => 'drones-returned',

            'Level'      => 'level',
            'Recursions' => 'recursions',

            'Kinetic Capsules Completed' => 'kinetic-capsules-completed',

            'Months Subscribed' => 'monthsSubscribed',

            'Umbra: Unique Resonator Slots Deployed' => 'currentChallenge',
            'Didact Fields Created'                  => 'currentChallenge',

            // Event 4/2021
            'Unique Event Portals Hacked'            => '',
            // Event 4/2021
            'Matryoshka Links Created'               => '',
        ];

    /**
     * @var array<string, array<int|string, array<int, string>|string>>
     */
    private array $customMedals
        = [
            'Anomaly' =>
                [
                    'Kythera',
                    'KurezeEffect',
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
                    'Persistence' => [],
                    'Resonance'   => [],
                    'Resurgence'  => [],
                    'Ouroboros'   => [],
                    'Sage'        => [],
                    'Luminary'    => [],
                    'Vanguard'    => [],
                    'Innovator'   => [],
                ],
            'Event'   =>
                [
                    'EOSImprint'           =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'CSans'                =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'CourierChallenge'     =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'KineticChallenge'     =>
                        [
                            'Bronze',
                            'Silver',
                            'Gold',
                        ],
                    'KnightTessellation'   =>
                        [
                            'Silver',
                            'Gold',
                        ],
                    'Paragon'              =>
                        [
                            '',
                        ],
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
                ],

        ];

    /**
     * @var array<string>
     */
    private array $discontinuedMedals = ['recruiter', 'seer'];

    /**
     * @var array<int, string>
     */
    private array $levelNames
        = [
            1 => 'Bronze',
            2 => 'Silver',
            3 => 'Gold',
            4 => 'Platinum',
            5 => 'Black',
        ];

    /**
     * @var array<int, string>
     */
    private array $translatedLevels = [];

    public function __construct(
        TranslatorInterface $translator,
        private readonly string $rootDir,
        private readonly string $appEnv,
    ) {
        $this->translatedLevels[1] = $translator->trans('medal.level.bronce');
        $this->translatedLevels[2] = $translator->trans('medal.level.silver');
        $this->translatedLevels[3] = $translator->trans('medal.level.gold');
        $this->translatedLevels[4] = $translator->trans('medal.level.platinum');
        $this->translatedLevels[5] = $translator->trans('medal.level.onyx');
    }

    /**
     * @return array<string, int>
     */
    public function checkLevels(AgentStat $agentStat): array
    {
        $levels = [];
        foreach ($this->medalLevels as $name => $level) {
            $methodName = $this->getGetterMethodName($name);
            if (method_exists($agentStat, $methodName)) {
                $levels[$name] = $this->getMedalLevel(
                    $name,
                    $agentStat->$methodName() ?: 0
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

    public function translatePrimeHeader(string $name): string
    {
        if (false === array_key_exists($name, $this->primeHeaders)) {
            if ('dev' === $this->appEnv) {
                throw new UnexpectedValueException(
                    sprintf('Prime header not found: "%s"', $name)
                );
            }

            return '';
        }

        return $this->primeHeaders[$name] ?? '';
    }

    public function getGetterMethodName(string $vName): string
    {
        return 'get'.implode('', array_map('ucfirst', explode('-', $vName)));
    }

    /**
     * @return array<string, int>
     */
    public function getUpgrades(
        AgentStat $previousEntry,
        AgentStat $currentEntry
    ): array {
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

    /**
     * @return array<string, int>
     */
    public function getDoubles(
        AgentStat $previousEntry,
        AgentStat $currentEntry
    ): array {
        $doubles = [];

        $currentLevels = $this->checkLevels($currentEntry);

        foreach ($currentLevels as $name => $currentLevel) {
            if (5 === $currentLevel) {
                $methodName = $this->getGetterMethodName($name);
                $currentDouble = $this->getDoubleValue(
                    $name,
                    $currentEntry->$methodName()
                );
                $previousDouble = $this->getDoubleValue(
                    $name,
                    $previousEntry->$methodName()
                );
                if ($currentDouble > $previousDouble && $currentDouble > 1) {
                    // DOUBLE!
                    $doubles[$name] = $currentDouble;
                }
            }
        }

        return $doubles;
    }

    public function getDescription(string $medal): string
    {
        return array_key_exists($medal, $this->medalLevels)
        && is_string($this->medalLevels[$medal]['desc'])
            ? $this->medalLevels[$medal]['desc'] : '';
    }

    public function getLevelValue(string $medal, int $level): int
    {
        if ('nl1331Meetups' === $medal) {
            $medal = 'nl-1331-meetups';
        }
        if ('mindController' === $medal) {
            $medal = 'mind-controller';
        }
        if ('scoutController' === $medal) {
            $medal = 'scout-controller';
        }

        return
            array_key_exists($medal, $this->medalLevels)
            && array_key_exists(
                $level - 1,
                (array)$this->medalLevels[$medal]['levels']
            )
                ? (int)$this->medalLevels[$medal]['levels'][$level - 1]
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

    public function translateMedalLevel(int $level): string
    {
        return $this->translatedLevels[$level] ?? (string)$level;
    }

    public function getDoubleValue(string $medal, int $value): int
    {
        $doubleValue = 0;

        if (5 === $this->getMedalLevel($medal, $value)) {
            $base = (int)$this->medalLevels[$medal]['levels'][4];

            $doubleValue = (int)($value / $base);
        }

        return $doubleValue;
    }

    public function getBadgePath(
        string $medal,
        int $level,
        int $size = 0,
        string $postFix = '.png'
    ): string {
        $medal = ucfirst($medal);
        $replacements = [
            'Mind-controller'  => 'MindController',
            'Recon'            => 'OPR',
            'Specops'          => 'SpecOps',
            'Missionday'       => 'MissionDayPrime',
            'Nl-1331-meetups'  => 'NL1331',
            'Nl1331Meetups'    => 'NL1331',
            'Ifs'              => 'FS',
            'Scout-controller' => 'ScoutController',
            'Second-sunday'    => 'SecondSunday',
        ];
        if (array_key_exists($medal, $replacements)) {
            $medal = $replacements[$medal];
        }

        $sizeString = $size ? '_'.$size : '';

        return 'Badge_'.$medal.'_'.$this->getLevelName($level).$sizeString
            .$postFix;
    }

    public function getChallengePath(string $medal, int $level): string
    {
        return 'EventBadge_'.$medal.'_'.$this->getLevelName($level);
    }

    /**
     * @return array<string, array<int|string, array<int, string>|string>>
     */
    public function getCustomMedalGroups(): array
    {
        return $this->customMedals;
    }

    /**
     * @return array<int, string>
     */
    public function getMedalLevelNames(): array
    {
        return $this->levelNames;
    }

    public function getMedalLevelName(int $level): string
    {
        return array_key_exists($level, $this->levelNames)
            ? $this->levelNames[$level] : '??';
    }

    /**
     * @throws JsonException
     */
    public function getBadgeData(string $code): BadgeData
    {
        static $badgeData;

        if (!$badgeData) {
            $badgeData = json_decode(
                (string)file_get_contents(
                    $this->rootDir.'/text-files/badgeinfos.json'
                ),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        foreach ($badgeData as $item) {
            if ($item['code'] === $code) {
                return new BadgeData($item);
            }
        }

        throw new UnexpectedValueException('No data for code: '.$code);
    }
}
