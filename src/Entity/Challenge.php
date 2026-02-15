<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use App\Repository\ChallengeRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: ChallengeRepository::class)]
class Challenge
{
    #[Column, Id, GeneratedValue(strategy: 'SEQUENCE')]
    private ?int $id = null;

    #[Column]
    private string $name = '';

    #[Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $date_start = null;

    #[Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $date_end = null;

    #[Column(nullable: true)]
    private ?string $code_name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
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
