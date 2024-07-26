<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\RoleRepository;

use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    use IdentifiableEntityTrait;
    use SluggableEntityTrait;

    #[ORM\Column]
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

    /**
     * @var Collection<User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'roles', fetch: 'EXTRA_LAZY')]
    private Collection $users;

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
        return $this->unflattenPermissions($this->permissions ?? []);
    }

    public function setPermissions(array $permissions): void
    {
        $this->permissions = $this->flattenPermissions($permissions);
    }

    private function flattenPermissions(array $permissions, string $prefix = ''): array
    {
        $flat = [];

        foreach ($permissions as $key => $value) {
            $fieldName = $prefix . $key;

            if (is_array($value)) {
                $flat = array_merge($flat, $this->flattenPermissions($value, $fieldName . '.'));
            } else {
                $flat[$fieldName] = $prefix . $value;
            }
        }

        return $flat;
    }

    private function unflattenPermissions(array $permissions): array
    {
        $nested = [];

        foreach ($permissions as $value) {
            $keys = explode('.', $value);
            $permission = array_pop($keys);
            $array =& $nested;

            foreach ($keys as $part) {
                if (!isset($array[$part])) {
                    $array[$part] = [];
                }
                $array =& $array[$part];
            }

            if (!in_array($permission, $array)) {
                $array[] = $permission;
            }
        }
        return $nested;
    }
}
