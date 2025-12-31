<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Api\Serializer\Attribute\Asset;
use Forumify\Core\Repository\UserRepository;
use Forumify\Forum\Entity\Badge;
use Forumify\Forum\Entity\Subscription;
use Forumify\OAuth\Entity\OAuthClient;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(operations: [])]
class User implements AuthorizableInterface, PasswordAuthenticatedUserInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    public function getUserId(): int
    {
        return $this->getId();
    }

    /** @var non-empty-string $username*/
    #[ORM\Column(length: 32, unique: true)]
    #[Groups(['MessageThread'])]
    private string $username;

    #[ORM\Column(length: 128, unique: true, nullable: true)]
    private ?string $email = null;

    /** @var Collection<int, Role> */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(
        'user_role',
        joinColumns: [new ORM\JoinColumn('user', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn('role', onDelete: 'CASCADE')],
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $roles;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 32)]
    #[Groups(['MessageThread'])]
    private string $displayName = '';

    /**
     * ISO 639-1 representation of the user's language
     */
    #[ORM\Column(length: 2, options: ['fixed' => true])]
    private string $language = 'en';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $timezone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['MessageThread'])]
    #[Asset('forumify.avatar')]
    private ?string $avatar = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $signature = null;

    #[ORM\Column(type: 'boolean')]
    private bool $emailVerified = false;

    #[ORM\Column(type: 'boolean')]
    private bool $banned = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $lastActivity = null;

    /** @var Collection<int, Subscription> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Subscription::class, fetch: 'EXTRA_LAZY')]
    private Collection $subscriptions;

    /** @var Collection<int, UserNotificationSettings> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserNotificationSettings::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private Collection $notificationSettings;

    /**
     * @var Collection<int, Badge>
     */
    #[ORM\ManyToMany(targetEntity: Badge::class, inversedBy: 'users', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(
        'user_badge',
        joinColumns: [new ORM\JoinColumn('user', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn('badge', onDelete: 'CASCADE')],
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $badges;

    #[ORM\OneToOne(targetEntity: OAuthClient::class, inversedBy: 'user')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?OAuthClient $oAuthClient = null;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->notificationSettings = new ArrayCollection([new UserNotificationSettings($this)]);
        $this->badges = new ArrayCollection();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        // @phpstan-ignore-next-line
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        return $this->username ?: 'unknown';
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    /**
     * @param Collection<int, Role>|array<Role> $roles
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
        return $this->password ?? '';
    }

    public function setPassword(?string $password): void
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

    public function getTimezone(): string
    {
        return $this->timezone ?? 'UTC';
    }

    public function setTimezone(?string $timezone): void
    {
        $this->timezone = $timezone;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): void
    {
        $this->signature = $signature;
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

    public function getLastActivity(): ?DateTime
    {
        return $this->lastActivity;
    }

    public function setLastActivity(DateTime $lastActivity): void
    {
        $this->lastActivity = $lastActivity;
    }

    public function isOnline(int $withinLastSeconds = 300): bool
    {
        $lastActivity = $this->getLastActivity();
        if ($lastActivity === null) {
            return false;
        }

        $diff = time() - $lastActivity->getTimestamp();
        return $diff < $withinLastSeconds;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function getNotificationSettings(): UserNotificationSettings
    {
        if ($this->notificationSettings->isEmpty()) {
            $this->notificationSettings = new ArrayCollection([new UserNotificationSettings($this)]);
        }
        /** @var UserNotificationSettings $settings */
        $settings = $this->notificationSettings->first();
        return $settings;
    }

    public function setNotificationSettings(UserNotificationSettings $notificationSettings): void
    {
        $this->notificationSettings = new ArrayCollection([$notificationSettings]);
    }

    /**
     * @return Collection<int, Badge>
     */
    public function getBadges(): Collection
    {
        return $this->badges;
    }

    /**
     * @param array<Badge>|Collection<int, Badge> $badges
     */
    public function setBadges(array|Collection $badges): void
    {
        $this->badges = $badges instanceof Collection
            ? $badges
            : new ArrayCollection($badges);
    }

    public function getOAuthClient(): ?OAuthClient
    {
        return $this->oAuthClient;
    }

    public function setOAuthClient(?OAuthClient $oAuthClient): void
    {
        $this->oAuthClient = $oAuthClient;
    }
}
