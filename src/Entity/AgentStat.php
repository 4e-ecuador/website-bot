<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

use App\Controller\Api\PostStats;

use ArrayAccess;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AgentStatRepository")
 *
 * @ApiFilter(SearchFilter::class, properties={"agent": "exact"})
 *
 * @ApiResource(
 *     attributes={
 *          "order"={"datetime": "DESC"}
 *     },
 *     itemOperations={
 *          "get"={
 *              "security"="is_granted('ROLE_AGENT')",
 *              "path"="/stats/{id}",
 *              "openapi_context"=AgentStat::API_GET_CONTEXT
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('ROLE_AGENT')",
 *              "path"="/stats",
 *              "openapi_context"=AgentStat::API_GET_CONTEXT
 *          },
 *          "post_csv"={
 *             "security"="is_granted('ROLE_AGENT')",
 *             "method"="POST",
 *             "path"="/stats/csv",
 *             "controller"=PostStats::class,
 *             "denormalization_context"={"groups"={"stats:write"}},
 *             "openapi_context"=AgentStat::API_POST_CSV_CONTEXT
 *          }
 *      },
 * )
 */
class AgentStat implements ArrayAccess
{
    public const API_GET_CONTEXT =[
        'security'=> ['name' => 'api_key'],

    ];
    public const API_POST_CSV_CONTEXT
        = [
            'summary'     => 'Create a AgentStat resource from CSV (actually TSV...)',
            'description' => '# Thanks Niantic for producing a CSV file with TABS :('
                ."\n"
                .'## hahaha'
                ."\n"
                .'![A great rabbit](https://rabbit.org/graphics/fun/netbunnies/jellybean1-brennan1.jpg)',
            'security'=> ['name' => 'api_key'],
            'responses'   => [
                '201' => [
                    'description' => 'Stats upload successful.',
                    'content'     => [
                        'application/json' => [
                            'schema'  =>
                                [
                                    'type'        => 'object',
                                    'description' => 'Stats import result',
                                    'properties'  => [
                                        'result' => [
                                            'type'       => 'object',
                                            'properties' => [
                                                'currents'   => [
                                                    'type'        => 'object',
                                                    'description' => 'Only on first import. Shows all medals and their levels.',
                                                    'items'       => [],
                                                ],
                                                'diff'       => [
                                                    'type'        => 'array',
                                                    'description' => 'Diff to previous import',
                                                    'items'       => [],

                                                ],
                                                'medalUps'   => [
                                                    'type'        => 'array',
                                                    'description' => 'Medals gained',
                                                    'items'       => [],

                                                ],
                                                'newLevel'   => [
                                                    'type'        => 'integer',
                                                    'description' => 'New level reached.',
                                                ],
                                                'recursions' => [
                                                    'type'        => 'integer',
                                                    'description' => 'Number of recursions.',
                                                ],
                                                'messages'   => [
                                                    'description' => 'Messages that have been sent during import.',
                                                    'type'        => 'array',
                                                    'items'       => [],

                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            'example' => <<<JSON
{
  "result": {
    "currents":{},
    "diff":[],
    "medalUps":[],
    "newLevel":0,
    "recursions":0,
    "messages":[]
  }
}
JSON,
                        ],
                    ],
                ],
                '409' => ['description' => 'Invalid CSV.'],
                '422' => ['description' => 'Stats already added.'],
                '428' => ['description' => 'Stats not ALL.'],
                '500' => ['description' => 'Server error.'],
                '503' => ['description' => 'Telegram bot failed.'],
            ],
        ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $datetime = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Agent")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Agent $agent = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $ap = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $explorer = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $recon = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $seer = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $trekker = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $builder = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $connector = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $mindController = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $illuminator = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $recharger = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $liberator = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $pioneer = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $engineer = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $purifier = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $specops = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $hacker = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $translator = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $sojourner = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $recruiter = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $missionday = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $nl1331Meetups = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $ifs = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $currentChallenge = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $level = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $scout = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $scoutController = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $longestLink = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $largestField = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $recursions = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $faction = '';

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private ?string $nickname = '';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $droneFlightDistance = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $droneHacks = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $dronePortalsVisited = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $droneForcedRecalls = null;

    public ?string $csv = '';

    /**
     * The raw CSV data (for API import)
     *
     * @Groups({"stats:write"})
     */
    public function setCsv(string $csv): self
    {
        $this->csv = $csv;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatetime(): ?DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    public function setAgent(?Agent $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getAp(): ?int
    {
        return $this->ap;
    }

    public function setAp(int $ap): self
    {
        $this->ap = $ap;

        return $this;
    }

    public function getExplorer(): ?int
    {
        return $this->explorer;
    }

    public function setExplorer(int $explorer): self
    {
        $this->explorer = $explorer;

        return $this;
    }

    public function getRecon(): ?int
    {
        return $this->recon;
    }

    public function setRecon(int $recon): self
    {
        $this->recon = $recon;

        return $this;
    }

    public function getSeer(): ?int
    {
        return $this->seer;
    }

    public function setSeer(?int $seer): self
    {
        $this->seer = $seer;

        return $this;
    }

    public function getTrekker(): ?int
    {
        return $this->trekker;
    }

    public function setTrekker(?int $trekker): self
    {
        $this->trekker = $trekker;

        return $this;
    }

    public function getBuilder(): ?int
    {
        return $this->builder;
    }

    public function setBuilder(?int $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function getConnector(): ?int
    {
        return $this->connector;
    }

    public function setConnector(?int $connector): self
    {
        $this->connector = $connector;

        return $this;
    }

    public function getMindController(): ?int
    {
        return $this->mindController;
    }

    public function setMindController(?int $mindController): self
    {
        $this->mindController = $mindController;

        return $this;
    }

    public function getIlluminator(): ?int
    {
        return $this->illuminator;
    }

    public function setIlluminator(?int $illuminator): self
    {
        $this->illuminator = $illuminator;

        return $this;
    }

    public function getRecharger(): ?int
    {
        return $this->recharger;
    }

    public function setRecharger(?int $recharger): self
    {
        $this->recharger = $recharger;

        return $this;
    }

    public function getLiberator(): ?int
    {
        return $this->liberator;
    }

    public function setLiberator(?int $liberator): self
    {
        $this->liberator = $liberator;

        return $this;
    }

    public function getPioneer(): ?int
    {
        return $this->pioneer;
    }

    public function setPioneer(?int $pioneer): self
    {
        $this->pioneer = $pioneer;

        return $this;
    }

    public function getEngineer(): ?int
    {
        return $this->engineer;
    }

    public function setEngineer(?int $engineer): self
    {
        $this->engineer = $engineer;

        return $this;
    }

    public function getPurifier(): ?int
    {
        return $this->purifier;
    }

    public function setPurifier(?int $purifier): self
    {
        $this->purifier = $purifier;

        return $this;
    }

    public function getSpecops(): ?int
    {
        return $this->specops;
    }

    public function setSpecops(?int $specops): self
    {
        $this->specops = $specops;

        return $this;
    }

    public function getHacker(): ?int
    {
        return $this->hacker;
    }

    public function setHacker(?int $hacker): self
    {
        $this->hacker = $hacker;

        return $this;
    }

    public function getTranslator(): ?int
    {
        return $this->translator;
    }

    public function setTranslator(?int $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    public function getSojourner(): ?int
    {
        return $this->sojourner;
    }

    public function setSojourner(?int $sojourner): self
    {
        $this->sojourner = $sojourner;

        return $this;
    }

    public function getRecruiter(): ?int
    {
        return $this->recruiter;
    }

    public function setRecruiter(?int $recruiter): self
    {
        $this->recruiter = $recruiter;

        return $this;
    }

    public function getMissionday(): ?int
    {
        return $this->missionday;
    }

    public function setMissionday(?int $missionday): self
    {
        $this->missionday = $missionday;

        return $this;
    }

    public function getNl1331Meetups(): ?int
    {
        return $this->nl1331Meetups;
    }

    public function setNl1331Meetups(?int $nl1331Meetups): self
    {
        $this->nl1331Meetups = $nl1331Meetups;

        return $this;
    }

    public function getIfs(): ?int
    {
        return $this->ifs;
    }

    public function setIfs(?int $ifs): self
    {
        $this->ifs = $ifs;

        return $this;
    }

    public function findProperties(): array
    {
        $props = [];

        foreach ($this as $index => $value) {
            if (false === in_array($index, ['id', 'datetime', 'agent'])) {
                $props[] = $index;
            }
        }

        return $props;
    }

    /**
     * Whether a offset exists
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        if (strpos($offset, '-')) {
            $offset = lcfirst(
                implode('', array_map('ucfirst', explode('-', $offset)))
            );
        }

        return property_exists($this, $offset);
    }

    /**
     * Offset to retrieve
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        $offset = lcfirst(
            implode('', array_map('ucfirst', explode('-', $offset)))
        );

        return $this->$offset;
    }

    /**
     * Offset to set
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value): void
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * Offset to unset
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset): void
    {
        // TODO: Implement offsetUnset() method.
    }

    public function computeDiff(AgentStat $previous): array
    {
        $diff = [];

        foreach ($this as $index => $value) {
            if ($this->$index > $previous->$index
                && (false === in_array(
                        $index,
                        [
                            'id',
                            'datetime',
                            'agent',
                            'faction',
                            'nickname',
                        ]
                    ))
            ) {
                $diff[$index] = $this->$index - $previous->$index;
            }
        }

        return $diff;
    }

    public function getCurrentChallenge(): ?int
    {
        return $this->currentChallenge;
    }

    public function setCurrentChallenge(?int $currentChallenge): self
    {
        $this->currentChallenge = $currentChallenge;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getScout(): ?int
    {
        return $this->scout;
    }

    public function setScout(?int $scout): self
    {
        $this->scout = $scout;

        return $this;
    }

    public function getLongestLink(): ?int
    {
        return $this->longestLink;
    }

    public function setLongestLink(?int $longestLink): self
    {
        $this->longestLink = $longestLink;

        return $this;
    }

    public function getLargestField(): ?int
    {
        return $this->largestField;
    }

    public function setLargestField(?int $largestField): self
    {
        $this->largestField = $largestField;

        return $this;
    }

    public function getRecursions(): ?int
    {
        return $this->recursions;
    }

    public function setRecursions(?int $recursions): self
    {
        $this->recursions = $recursions;

        return $this;
    }

    public function getFaction(): ?string
    {
        return $this->faction;
    }

    public function setFaction(?string $faction): self
    {
        $this->faction = $faction;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getDroneFlightDistance(): ?int
    {
        return $this->droneFlightDistance;
    }

    public function setDroneFlightDistance(?int $droneFlightDistance): self
    {
        $this->droneFlightDistance = $droneFlightDistance;

        return $this;
    }

    public function getDroneHacks(): ?int
    {
        return $this->droneHacks;
    }

    public function setDroneHacks(?int $droneHacks): self
    {
        $this->droneHacks = $droneHacks;

        return $this;
    }

    public function getDronePortalsVisited(): ?int
    {
        return $this->dronePortalsVisited;
    }

    public function setDronePortalsVisited(?int $drone_portals_visited): self
    {
        $this->dronePortalsVisited = $drone_portals_visited;

        return $this;
    }

    public function getScoutController(): ?int
    {
        return $this->scoutController;
    }

    public function setScoutController(?int $scoutController): self
    {
        $this->scoutController = $scoutController;

        return $this;
    }

    public function getDroneForcedRecalls(): ?int
    {
        return $this->droneForcedRecalls;
    }

    public function setDroneForcedRecalls(?int $droneForcedRecalls): self
    {
        $this->droneForcedRecalls = $droneForcedRecalls;

        return $this;
    }
}
