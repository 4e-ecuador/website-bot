<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\FactionRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: FactionRepository::class)]
class Faction
{
    #[Column, Id, GeneratedValue]
    private ?int $id = null;

    #[Column]
    private ?string $name = null;

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
}
