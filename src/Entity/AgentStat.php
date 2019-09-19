<?php

namespace App\Entity;

use ArrayAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AgentStatRepository")
 */
class AgentStat implements ArrayAccess
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datetime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Agent")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agent;

    /**
     * @ORM\Column(type="integer")
     */
    private $ap;

    /**
     * @ORM\Column(type="integer")
     */
    private $explorer;

    /**
     * @ORM\Column(type="integer")
     */
    private $recon;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $seer;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $trekker;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $builder;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $connector;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mindController;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $illuminator;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $recharger;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $liberator;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pioneer;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $engineer;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $purifier;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $specops;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $hacker;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $translator;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sojourner;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $recruiter;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $missionday;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nl1331Meetups;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ifs;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): self
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

    public function getProperties()
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
    public function offsetExists($offset)
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
    public function offsetSet($offset, $value)
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
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    public function getDiff(AgentStat $previous): array
    {
        $diff = [];

        foreach ($this as $index => $value) {
            if (false === in_array($index, ['id', 'datetime', 'agent'])) {
                if ($this->$index > $previous->$index) {
                    $diff[$index] = $this->$index - $previous->$index;
                }
            }
        }

        return $diff;
    }
}
