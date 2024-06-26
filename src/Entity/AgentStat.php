<?php

namespace App\Entity;

use ArrayAccess;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use App\Repository\AgentStatRepository;
use ReflectionClass;

/**
 * @implements ArrayAccess<int, int>
 */
#[Entity(repositoryClass: AgentStatRepository::class)]
class AgentStat implements ArrayAccess
{
    #[Column, Id, GeneratedValue(strategy: 'SEQUENCE')]
    private ?int $id = null;

    #[Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $datetime = null;

    #[ManyToOne]
    #[JoinColumn(nullable: false)]
    private ?Agent $agent = null;

    #[Column]
    private ?int $ap = null;

    #[Column(nullable: true)]
    private ?int $explorer = null;

    #[Column(nullable: true)]
    private ?int $recon = null;

    #[Column(nullable: true)]
    private ?int $seer = null;

    #[Column(nullable: true)]
    private ?int $trekker = null;

    #[Column(nullable: true)]
    private ?int $builder = null;

    #[Column(nullable: true)]
    private ?int $connector = null;

    #[Column(nullable: true)]
    private ?int $mindController = null;

    #[Column(nullable: true)]
    private ?int $illuminator = null;

    #[Column(nullable: true)]
    private ?int $recharger = null;

    #[Column(nullable: true)]
    private ?int $liberator = null;

    #[Column(nullable: true)]
    private ?int $pioneer = null;

    #[Column(nullable: true)]
    private ?int $engineer = null;

    #[Column(nullable: true)]
    private ?int $purifier = null;

    #[Column(nullable: true)]
    private ?int $specops = null;

    #[Column(nullable: true)]
    private ?int $hacker = null;

    #[Column(nullable: true)]
    private ?int $translator = null;

    #[Column(nullable: true)]
    private ?int $sojourner = null;

    #[Column(nullable: true)]
    private ?int $recruiter = null;

    #[Column(nullable: true)]
    private ?int $missionday = null;

    #[Column(nullable: true)]
    private ?int $nl1331Meetups = null;

    #[Column(nullable: true)]
    private ?int $ifs = null;

    #[Column(nullable: true)]
    private ?int $currentChallenge = null;

    #[Column(nullable: true)]
    private ?int $level = null;

    #[Column(nullable: true)]
    private ?int $scout = null;

    #[Column(nullable: true)]
    private ?int $scoutController = null;

    #[Column(nullable: true)]
    private ?int $longestLink = null;

    #[Column(nullable: true)]
    private ?int $largestField = null;

    #[Column(nullable: true)]
    private ?int $recursions = null;

    #[Column(length: 50, nullable: true)]
    private ?string $faction = '';

    #[Column(length: 150, nullable: true)]
    private ?string $nickname = '';

    #[Column(nullable: true)]
    private ?int $droneFlightDistance = null;

    #[Column(nullable: true)]
    private ?int $maverick = null;

    #[Column(nullable: true)]
    private ?int $dronePortalsVisited = null;

    #[Column(nullable: true)]
    private ?int $droneForcedRecalls = null;

    #[Column(nullable: true)]
    private ?int $kineticCapsulesCompleted = null;

    #[Column(nullable: true)]
    private ?int $monthsSubscribed = null;

    #[Column(nullable: true)]
    private ?int $epoch = null;

    #[Column(nullable: true)]
    private ?int $portalsDiscovered = null;

    #[Column(nullable: true)]
    private ?int $dronesReturned = null;

    #[Column(nullable: true)]
    private ?int $secondSunday = null;

    #[Column(nullable: true)]
    private ?int $reclaimer = null;

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

    /**
     * @return array<string>
     */
    public function findProperties(): array
    {
        $properties = [];

        $class = new ReflectionClass(self::class);

        foreach ($class->getProperties() as $property) {
            if (in_array($property->getName(), ['id', 'datetime', 'agent'])) {
                continue;
            }

            $properties[] = $property->getName();
        }

        return $properties;
    }

    /**
     * Whether a offset exists
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists(mixed $offset): bool
    {
        if (strpos((string) $offset, '-')) {
            $offset = lcfirst(
                implode('', array_map('ucfirst', explode('-', (string)$offset)))
            );
        }

        return property_exists($this, (string)$offset);
    }

    /**
     * Offset to retrieve
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet(mixed $offset): mixed
    {
        $offset = lcfirst(
            implode('', array_map('ucfirst', explode('-', (string)$offset)))
        );

        return $this->$offset;
    }

    /**
     * Offset to set
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @since 5.0.0
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * Offset to unset
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @since 5.0.0
     */
    public function offsetUnset(mixed $offset): void
    {
        // TODO: Implement offsetUnset() method.
    }

    /**
     * @return array<string, int>
     */
    public function computeDiff(AgentStat $previous): array
    {
        $diff = [];

        foreach ($this->findProperties() as $property) {
            if ($this->$property <= $previous->$property) {
                continue;
            }

            if (in_array($property, [
                    'id',
                    Types::DATETIME_MUTABLE,
                    'agent',
                    'faction',
                    'nickname',
                ], true)) {
                continue;
            }

            $diff[$property] = $this->$property - $previous->$property;
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

    public function getMaverick(): ?int
    {
        return $this->maverick;
    }

    public function setMaverick(?int $maverick): self
    {
        $this->maverick = $maverick;

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

    public function getKineticCapsulesCompleted(): ?int
    {
        return $this->kineticCapsulesCompleted;
    }

    public function setKineticCapsulesCompleted(?int $kineticCapsulesCompleted
    ): self {
        $this->kineticCapsulesCompleted = $kineticCapsulesCompleted;

        return $this;
    }

    public function getMonthsSubscribed(): ?int
    {
        return $this->monthsSubscribed;
    }

    public function setMonthsSubscribed(?int $monthsSubscribed): self
    {
        $this->monthsSubscribed = $monthsSubscribed;

        return $this;
    }

    public function getEpoch(): ?int
    {
        return $this->epoch;
    }

    public function setEpochHackstreaks(?int $epoch): self
    {
        $this->epoch = $epoch;

        return $this;
    }

    public function getPortalsDiscovered(): ?int
    {
        return $this->portalsDiscovered;
    }

    public function setPortalsDiscovered(?int $portalsDiscovered): self
    {
        $this->portalsDiscovered = $portalsDiscovered;

        return $this;
    }

    public function getDronesReturned(): ?int
    {
        return $this->dronesReturned;
    }

    public function setDronesReturned(?int $dronesReturned): self
    {
        $this->dronesReturned = $dronesReturned;

        return $this;
    }

    public function getSecondSunday(): ?int
    {
        return $this->secondSunday;
    }

    public function setSecondSunday(?int $secondSunday): self
    {
        $this->secondSunday = $secondSunday;

        return $this;
    }

    public function getReclaimer(): ?int
    {
        return $this->reclaimer;
    }

    public function setReclaimer(?int $reclaimer): self
    {
        $this->reclaimer = $reclaimer;

        return $this;
    }
}
