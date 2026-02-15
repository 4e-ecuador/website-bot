<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\HelpRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: HelpRepository::class)]
class Help
{
    #[Column, Id, GeneratedValue(strategy: 'SEQUENCE')]
    private ?int $id = null;

    #[Column(type: Types::TEXT)]
    private string $text = '';

    #[Column]
    private string $slug = '';

    #[Column]
    private string $title = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
