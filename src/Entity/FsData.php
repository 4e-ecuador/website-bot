<?php

namespace App\Entity;

use App\Repository\FsDataRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FsDataRepository::class)]
class FsData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $attendeesCount;

    #[ORM\Column(type: 'json')]
    private $data = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    public function __construct() {
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

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
