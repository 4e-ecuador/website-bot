<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
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
    private ?string $text = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Agent", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Agent $agent = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $datetime = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $commenter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getAgent(): ?agent
    {
        return $this->agent;
    }

    public function setAgent(?agent $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getDatetime(): ?DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getCommenter(): ?user
    {
        return $this->commenter;
    }

    public function setCommenter(?user $commenter): self
    {
        $this->commenter = $commenter;

        return $this;
    }
}
