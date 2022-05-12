<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use App\Repository\EventRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[Column, Id, GeneratedValue]
    private ?int $id = null;

    #[Column]
    private ?string $name = null;

    #[Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $date_start = null;

    #[Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $date_end = null;

    #[Column(nullable: true)]
    private ?string $event_type = null;

    #[Column(nullable: true)]
    private ?string $recurring = null;

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

    public function getEventType(): ?string
    {
        return $this->event_type;
    }

    public function setEventType(?string $Event_type): self
    {
        $this->event_type = $Event_type;

        return $this;
    }

    public function getRecurring(): ?string
    {
        return $this->recurring;
    }

    public function setRecurring(?string $recurring): self
    {
        $this->recurring = $recurring;

        return $this;
    }
}
