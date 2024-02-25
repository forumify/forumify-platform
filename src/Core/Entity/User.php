<?php

namespace Forumify\Core\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\UserRepository;
use Forumify\Forum\Entity\Subscription;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(length: 32, unique: true)]
    private string $username;

    #[ORM\Column(length: 128, unique: true)]
    private string $email;

    /** @var Collection<Role> */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(
        'user_role',
        joinColumns: [new ORM\JoinColumn('user', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn('role', onDelete: 'CASCADE')],
    )]
    private Collection $roles;

    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 32)]
    private string $displayName;

    /**
     * ISO 639-1 representation of the user's language
     */
    #[ORM\Column(length: 2)]
    private string $language = 'en';

    #[ORM\Column(nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: 'boolean')]
    private bool $emailVerified = false;

    #[ORM\Column(type: 'boolean')]
    private bool $banned = false;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $lastLogin = null;

    /** @var Collection<Subscription> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Subscription::class, fetch: 'EXTRA_LAZY')]
    private Collection $subscriptions;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserNotificationSettings::class, cascade: ['persist'])]
    private UserNotificationSettings $notificationSettings;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->notificationSettings = new UserNotificationSettings($this);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        foreach ($this->roles as $role) {
            $roles[] = $role->getRoleName();
        }

        return array_unique($roles);
    }

    /**
     * @return Collection<Role>
     */
    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    /**
     * @param Collection<Role>|array<Role> $roles
     */
    public function setRoleEntities(Collection|array $roles): void
    {
        $this->roles = $roles instanceof Collection
            ? $roles
            : new ArrayCollection($roles);
    }

    public function addRoleEntity(Role $role): void
    {
        $this->roles->add($role);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getDisplayName(): string
    {
        return $this->displayName ?: $this->getUsername();
    }

    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function eraseCredentials(): void
    {
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $emailVerified): void
    {
        $this->emailVerified = $emailVerified;
    }

    public function isBanned(): bool
    {
        return $this->banned;
    }

    public function setBanned(bool $banned): void
    {
        $this->banned = $banned;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function getNotificationSettings(): UserNotificationSettings
    {
        return $this->notificationSettings ?? new UserNotificationSettings($this);
    }

    public function setNotificationSettings(UserNotificationSettings $notificationSettings): void
    {
        $this->notificationSettings = $notificationSettings;
    }
}
