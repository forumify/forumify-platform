<?php

declare(strict_types=1);

namespace Forumify\OAuth\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\User;
use Forumify\OAuth\Repository\IdentityProviderUserRepository;

#[ORM\Entity(repositoryClass: IdentityProviderUserRepository::class)]
#[ORM\UniqueConstraint(name: 'user_idp_uniq', fields: ['user', 'identityProvider'])]
#[ORM\UniqueConstraint(name: 'extid_idp_uniq', fields: ['externalIdentifier', 'identityProvider'])]
class IdentityProviderUser
{
    use IdentifiableEntityTrait;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: IdentityProvider::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false)]
    private IdentityProvider $identityProvider;

    #[ORM\Column(length: 255, index: true, nullable: false)]
    private string $externalIdentifier = '';

    #[ORM\Column(length: 255, nullable: false)]
    private string $externalUsername = '';

    public function __construct(
        User $user,
        IdentityProvider $idp,
        string $externalIdentifier,
        string $externalUsername,
    ) {
        assert(!empty($externalIdentifier));
        $this->user = $user;
        $this->identityProvider = $idp;
        $this->externalIdentifier = $externalIdentifier;
        $this->externalUsername = $externalUsername;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getIdentityProvider(): IdentityProvider
    {
        return $this->identityProvider;
    }

    public function setIdentityProvider(IdentityProvider $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }

    public function getExternalIdentifier(): string
    {
        return $this->externalIdentifier;
    }

    public function setExternalIdentifier(string $externalIdentifier): void
    {
        $this->externalIdentifier = $externalIdentifier;
    }

    public function getExternalUsername(): string
    {
        return $this->externalUsername;
    }

    public function setExternalUsername(string $externalUsername): void
    {
        $this->externalUsername = $externalUsername;
    }
}
