<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TestStatRepository")
 */
class TestStat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $csv;

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
