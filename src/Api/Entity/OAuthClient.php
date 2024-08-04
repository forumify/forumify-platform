<?php

declare(strict_types=1);

namespace Forumify\Api\Entity;

use Doctrine\Common\Collections\Collection;
use Forumify\Api\Repository\OAuthClientRepository;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Core\Entity\UserPermissionsTrait;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Security\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: OAuthClientRepository::class)]
#[ORM\Table('oauth_client')]
class OAuthClient implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;
    use UserPermissionsTrait;

    #[ORM\Column(length: 32, unique: true)]
    private string $clientId;

    #[ORM\Column]
    private string $clientSecret;

    /** @var Collection<Role> */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'clients', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(
        'oauth_client_role',
        joinColumns: [new ORM\JoinColumn('client', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn('role', onDelete: 'CASCADE')],
    )]
    private Collection $roles;

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
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function getPassword(): ?string
    {
        return $this->getClientSecret();
    }

    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    public function setRoles(Collection $roles): void
    {
        $this->roles = $roles;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER', 'ROLE_OAUTH_CLIENT'];
        foreach ($this->roles as $role) {
            $roles[] = $role->getRoleName();
        }

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
    }
}
