<?php

declare(strict_types=1);

namespace Forumify\OAuth\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\AuthorizableInterface;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\Role;
use Forumify\OAuth\Repository\OAuthClientRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OAuthClientRepository::class)]
class OAuthClient implements AuthorizableInterface
{
    use IdentifiableEntityTrait;

    #[Assert\NotBlank(allowNull: false)]
    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    private string $clientId = '';

    #[ORM\Column(length: 255)]
    private string $clientSecret = '';

    /**
     * @var array<string>
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $redirectUris = [];

    /** @var Collection<int, Role> */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'clients', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(
        'oauth_client_role',
        joinColumns: [new ORM\JoinColumn('client', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn('role', onDelete: 'CASCADE')],
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $roles;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $lastActivity = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
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
        return $this->roles;
    }

    public function setRoleEntities(Collection $roles): void
    {
        $this->roles = $roles;
    }

    public function getLastActivity(): ?DateTime
    {
        return $this->lastActivity;
    }

    public function setLastActivity(DateTime $lastActivity): void
    {
        $this->lastActivity = $lastActivity;
    }
}
