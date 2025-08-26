<?php

namespace App\Entity;

use App\Repository\FsDataRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: FsDataRepository::class)]
class FsData
{
    #[Column, Id, GeneratedValue(strategy: 'SEQUENCE')]
    private ?int $id = null;

    #[Column(type: 'integer')]
    private ?int $attendeesCount = null;

    #[Column(type: 'json')]
    private ?string $data = null;

    #[Column(type: 'datetime_immutable')]
    private ?DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttendeesCount(): ?int
    {
        return $this->attendeesCount;
    }

    public function setAttendeesCount(int $attendeesCount): self
    {
        $this->attendeesCount = $attendeesCount;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
