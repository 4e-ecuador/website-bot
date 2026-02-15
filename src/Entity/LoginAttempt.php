<?php

namespace App\Entity;

use App\Repository\LoginAttemptRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: LoginAttemptRepository::class)]
class LoginAttempt
{
    #[Column, Id, GeneratedValue(strategy: 'SEQUENCE')]
    private ?int $id = null;

    #[Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $createdAt;

    #[Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[Column]
    private bool $success = false;

    #[Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[Column(length: 30)]
    private string $authMethod = 'unknown';

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
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

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getAuthMethod(): string
    {
        return $this->authMethod;
    }

    public function setAuthMethod(string $authMethod): self
    {
        $this->authMethod = $authMethod;

        return $this;
    }
}
