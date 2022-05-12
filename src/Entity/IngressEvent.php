<?php

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use App\Repository\IngressEventRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: IngressEventRepository::class)]
class IngressEvent
{
    #[Column, Id, GeneratedValue]
    private ?int $id = null;

    #[Column]
    private ?string $name = '';

    #[Column]
    private ?string $type = '';

    #[Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $date_start = null;

    #[Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $date_end = null;

    #[Column(type: Types::TEXT, nullable: true)]
    private ?string $description = '';

    #[Column(nullable: true)]
    private ?string $link = '';

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateStart(): ?DateTime
    {
        return $this->date_start;
    }

    public function setDateStart(DateTime $date_start): self
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?DateTime
    {
        return $this->date_end;
    }

    public function setDateEnd(DateTime $date_end): self
    {
        $this->date_end = $date_end;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }
}
