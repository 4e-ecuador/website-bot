<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Controller\Api\GetMeAction;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="agent_user")
 *
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={
 *         "get_me"={
 *             "security"="is_granted('ROLE_AGENT')",
 *             "method"="GET",
 *             "path"="/users/me",
 *             "controller"=GetMeAction::class,
 *             "openapi_context"={
 *                 "parameters"={}
 *             },
 *             "read"=false
 *         }
 *     },
 *     normalizationContext={"groups"={"me:read"}}
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({"me:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Agent", cascade={"persist", "remove"})
     *
     * @Groups({"me:read"})
     */
    private $agent;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $googleId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fireBaseToken;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $apiToken;

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

    public function hasRole()
    {
        return $this->getRoles();
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPassword()
    {
    }

    public function getUsername()
    {
        return (string)$this->email;
    }

    public function __toString()
    {
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
}
