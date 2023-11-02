<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\ACLRepository;

#[ORM\Entity(repositoryClass: ACLRepository::class)]
#[ORM\UniqueConstraint('entity_uniq', fields: ['entity', 'entityId', 'permission'])]
class ACL
{
    use IdentifiableEntityTrait;

    #[ORM\Column]
    private string $entity;

    #[ORM\Column]
    private string $entityId;

    #[ORM\Column]
    private string $permission;

    /** @var Collection<Role> */
    #[ORM\ManyToMany(targetEntity: Role::class, cascade: ['persist'])]
    #[ORM\JoinTable(
        'acl_role',
        joinColumns: [new ORM\JoinColumn('acl', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn('role', onDelete: 'CASCADE')],
    )]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): void
    {
        $this->entityId = $entityId;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): void
    {
        $this->permission = $permission;
    }

    /**
     * @return Collection<Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * @param Collection<Role>|array<Role> $roles
     */
    public function setRoles(Collection|array $roles): void
    {
        $this->roles = $roles instanceof Collection
            ? $roles
            : new ArrayCollection($roles);
    }
}
