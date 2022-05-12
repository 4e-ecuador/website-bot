<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\TestStatRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: TestStatRepository::class)]
class TestStat
{
    #[Column, Id, GeneratedValue]
    private ?int $id = null;

    #[Column(type: Types::TEXT)]
    private ?string $csv = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCsv(): ?string
    {
        return $this->csv;
    }

    public function setCsv(string $csv): self
    {
        $this->csv = $csv;

        return $this;
    }
}
