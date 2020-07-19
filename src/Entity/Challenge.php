<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChallengeRepository")
 */
class Challenge
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $date_start = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $date_end = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $code_name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDateStart(): ?DateTimeInterface
    {
        return $this->date_start;
    }

    public function setDateStart(DateTimeInterface $date_start): self
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(DateTimeInterface $date_end): self
    {
        $this->date_end = $date_end;

        return $this;
    }

    public function getCodeName(): ?string
    {
        return $this->code_name;
    }

    public function setCodeName(?string $code_name): self
    {
        $this->code_name = $code_name;

        return $this;
    }
}
