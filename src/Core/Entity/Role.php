<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\RoleRepository;
use Forumify\OAuth\Entity\OAuthClient;

use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role implements SortableEntityInterface
{
    use IdentifiableEntityTrait;
    use SluggableEntityTrait;
    use SortableEntityTrait;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\Column(type: 'boolean')]
    private bool $administrator = false;

    #[ORM\Column(type: 'boolean')]
    private bool $moderator = false;

    #[ORM\Column(name: '`system`', type: 'boolean')]
    private bool $system = false;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $permissions = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $showOnForum = false;

    #[ORM\Column(length: 7, nullable: true, options: ['fixed' => true])]
    private ?string $color = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $showOnUsername = false;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'roles', fetch: 'EXTRA_LAZY')]
    private Collection $users;

    /**
     * @var Collection<int, OAuthClient>
     */
    #[ORM\ManyToMany(targetEntity: OAuthClient::class, mappedBy: 'roles', fetch: 'EXTRA_LAZY')]
    private Collection $clients;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getRoleName(): string
    {
        return u($this->getSlug())
            ->replace('-', '_')
            ->upper()
            ->ensureStart('ROLE_')
            ->toString();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isAdministrator(): bool
    {
        return $this->administrator;
    }

    public function setAdministrator(bool $administrator): void
    {
        $this->administrator = $administrator;
    }

    public function isModerator(): bool
    {
        return $this->moderator;
    }

    public function setModerator(bool $moderator): void
    {
        $this->moderator = $moderator;
    }

    public function isSystem(): bool
    {
        return $this->system;
    }

    /**
     * @return Collection<User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUsers(Collection $users): void
    {
        $this->users = $users;
    }

    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }

    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function isShowOnForum(): bool
    {
        return $this->showOnForum;
    }

    public function setShowOnForum(bool $showOnForum): void
    {
        $this->showOnForum = $showOnForum;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function isShowOnUsername(): bool
    {
        return $this->showOnUsername;
    }

    public function setShowOnUsername(bool $showOnUsername): void
    {
        $this->showOnUsername = $showOnUsername;
    }

    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function setClients(Collection $clients): void
    {
        $this->clients = $clients;
    }
}
