<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;

#[Entity(repositoryClass: UserRepository::class)]
#[Table(name: 'agent_user')]
#[UniqueEntity(fields: 'identifier', message: 'This identifier is already in use')]
class User implements UserInterface, \Stringable
{
    #[Column, Id, GeneratedValue]
    private ?int $id = null;

    /**
     * @var array<string>
     */
    #[Column(type: Types::JSON)]
    private array $roles = [];

    #[Column(unique: true)]
    private ?string $email = '';

    #[OneToOne(cascade: ['persist', 'remove'])]
    private ?Agent $agent = null;

    #[Column(length: 100, nullable: true)]
    private ?string $googleId = null;

    #[Column(nullable: true)]
    private ?string $fireBaseToken = null;

    #[Column(nullable: true)]
    private ?string $apiToken = '';

    #[Column(nullable: true)]
    private ?string $avatar = null;

    #[Column(type: Types::TEXT, nullable: true)]
    private ?string $avatarEncoded = null;

    public function __toString(): string
    {
        return (string)$this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @return array<string>
     */
    public function hasRole(): array
    {
        return $this->getRoles();
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return (string)$this->email;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getUserAgentName(): string
    {
        if ($this->agent) {
            return sprintf(
                '%s <%s>',
                $this->agent->getNickname(),
                $this->email
            );
        }

        return (string)$this->email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    public function setAgent(?Agent $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getFireBaseToken(): ?string
    {
        return $this->fireBaseToken;
    }

    public function setFireBaseToken(?string $fireBaseToken): self
    {
        $this->fireBaseToken = $fireBaseToken;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatarEncoded(): ?string
    {
        return $this->avatarEncoded;
    }

    public function setAvatarEncoded(?string $avatarEncoded): self
    {
        $this->avatarEncoded = $avatarEncoded;

        return $this;
    }
}
