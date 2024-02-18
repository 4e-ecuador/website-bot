<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Stringable;
use App\Repository\AgentRepository;

#[Entity(repositoryClass: AgentRepository::class)]
class Agent implements Stringable
{
    #[Column, Id, GeneratedValue]
    protected ?int $id = null;

    #[Column]
    protected ?string $nickname = '';

    #[Column(nullable: true)]
    private ?string $realName = '';

    #[Column(type: 'decimal', precision: 10, scale: 6, nullable: true)]
    private ?string $lat = null;

    #[Column(type: 'decimal', precision: 10, scale: 6, nullable: true)]
    private ?string $lon = null;

    #[ManyToOne]
    #[JoinColumn(nullable: false)]
    private ?Faction $faction = null;

    /**
     * @var Collection<int, Comment>
     */
    #[OneToMany(mappedBy: 'agent', targetEntity: Comment::class)]
    private Collection $comments;

    #[Column(type: Types::TEXT, nullable: true)]
    private ?string $custom_medals = '';

    #[ManyToOne(inversedBy: 'agents')]
    private ?MapGroup $map_group = null;

    #[Column(nullable: true)]
    private ?string $telegram_name = '';

    #[Column(nullable: true)]
    private ?int $telegram_id = null;

    #[Column(length: 48, nullable: true)]
    private ?string $telegram_connection_secret = null;

    #[Column(nullable: true)]
    private ?bool $hasNotifyUploadStats = null;

    #[Column(nullable: true)]
    private ?bool $hasNotifyEvents = null;

    #[Column(nullable: true)]
    private ?bool $hasNotifyStatsResult = null;

    #[Column(length: 2, nullable: true)]
    private ?string $locale = '';

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(?string $realName): self
    {
        $this->realName = $realName;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(string $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?string
    {
        return $this->lon;
    }

    public function setLon(string $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function setFaction(?Faction $faction): self
    {
        $this->faction = $faction;

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->nickname;
    }

    public function __sleep(): array
    {
        return ['id'];
    }

    /**
     * @return Collection<int, Comment>|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAgent($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAgent() === $this) {
                $comment->setAgent(null);
            }
        }

        return $this;
    }

    public function getCustomMedals(): ?string
    {
        return $this->custom_medals;
    }

    public function setCustomMedals(?string $custom_medals): self
    {
        $this->custom_medals = $custom_medals;

        return $this;
    }

    public function getMapGroup(): ?MapGroup
    {
        return $this->map_group;
    }

    public function setMapGroup(?MapGroup $map_group): self
    {
        $this->map_group = $map_group;

        return $this;
    }

    public function getTelegramName(): ?string
    {
        return $this->telegram_name;
    }

    public function setTelegramName(?string $telegram_name): self
    {
        $this->telegram_name = $telegram_name;

        return $this;
    }

    public function getTelegramId(): ?int
    {
        return $this->telegram_id;
    }

    public function setTelegramId(?int $telegram_id): self
    {
        $this->telegram_id = $telegram_id;

        return $this;
    }

    public function getTelegramConnectionSecret(): ?string
    {
        return $this->telegram_connection_secret;
    }

    public function setTelegramConnectionSecret(
        ?string $telegram_connection_secret
    ): self {
        $this->telegram_connection_secret = $telegram_connection_secret;

        return $this;
    }

    public function getHasNotifyUploadStats(): ?bool
    {
        return $this->hasNotifyUploadStats;
    }

    public function setHasNotifyUploadStats(?bool $hasNotifyUploadStats): self
    {
        $this->hasNotifyUploadStats = $hasNotifyUploadStats;

        return $this;
    }

    public function getHasNotifyEvents(): ?bool
    {
        return $this->hasNotifyEvents;
    }

    public function setHasNotifyEvents(?bool $hasNotifyEvents): self
    {
        $this->hasNotifyEvents = $hasNotifyEvents;

        return $this;
    }

    public function getHasNotifyStatsResult(): ?bool
    {
        return $this->hasNotifyStatsResult;
    }

    public function setHasNotifyStatsResult(?bool $hasNotifyStatsResult): self
    {
        $this->hasNotifyStatsResult = $hasNotifyStatsResult;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }
}
