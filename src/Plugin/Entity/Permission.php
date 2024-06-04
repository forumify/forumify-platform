<?php

declare(strict_types=1);

namespace Forumify\Plugin\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\PermissionRepository;


#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{
    use IdentifiableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $permission;

    #[ORM\ManyToOne(targetEntity: Plugin::class, inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plugin $plugin = null;

    /**
     * @var Collection|Role[]
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'permissions')]
    #[ORM\JoinTable(name: 'role_permissions')]
    private Collection $roles;

    public function getId(): int
    {
        return $this->id;
    }


    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): void
    {
        $this->permission = $permission;
    }

    public function getPlugin(): ?Plugin
    {
        return $this->plugin;
    }

    public function setPlugin(?Plugin $plugin): void
    {
        $this->plugin = $plugin;
    }

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->addPermission($this);
        }
    }

    public function removeRole(Role $role): void
    {
        if ($this->roles->removeElement($role)) {
            $role->removePermission($this);
        }
    }

}
