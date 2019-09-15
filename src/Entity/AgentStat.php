<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AgentStatRepository")
 */
class AgentStat
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
}
