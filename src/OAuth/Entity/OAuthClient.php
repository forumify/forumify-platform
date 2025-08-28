<?php

declare(strict_types=1);

namespace Forumify\OAuth\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\AuthorizableInterface;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\User;
use Forumify\OAuth\Repository\OAuthClientRepository;

#[ORM\Entity(repositoryClass: OAuthClientRepository::class)]
class OAuthClient implements AuthorizableInterface
{
    use IdentifiableEntityTrait;

    #[ORM\Column(length: 255, unique: true)]
    private string $clientId = '';

    #[ORM\Column(length: 255)]
    private string $clientSecret = '';

    /**
     * @var array<string>
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $redirectUris = [];

    #[ORM\OneToOne(targetEntity: User::class, mappedBy: 'oAuthClient', fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    private User $user;

    public function __construct()
    {
        $this->user = new User();
        $this->user->setOAuthClient($this);
    }

    public function getName(): string
    {
        return $this->user->getDisplayName();
    }

    public function setName(string $name): void
    {
        $this->user->setDisplayName($name);
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
        $this->user->setUsername($clientId);
    }

    public function getUserIdentifier(): string
    {
        return $this->getClientId();
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return array<string>
     */
    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    /**
     * @param array<string> $redirectUris
     */
    public function setRedirectUris(array $redirectUris): void
    {
        $this->redirectUris = $redirectUris;
    }

    /** @inheritDoc */
    public function getRoles(): array
    {
        return ['ROLE_USER', 'ROLE_OAUTH_CLIENT'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getRoleEntities(): Collection
    {
        return $this->user->getRoleEntities();
    }

    public function setRoleEntities(Collection $roles): void
    {
        $this->user->setRoleEntities($roles);
    }

    public function getLastActivity(): ?DateTime
    {
        return $this->user->getLastActivity();
    }

    public function setLastActivity(DateTime $lastActivity): void
    {
        $this->user->setLastActivity($lastActivity);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $User): void
    {
        $this->user = $User;
    }
}
