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

    #[ORM\Column(length: 255)]
    private string $entity;

    #[ORM\Column(length: 255)]
    private string $entityId;

    #[ORM\Column(length: 255)]
    private string $permission;

    /** @var Collection<int, Role> */
    #[ORM\ManyToMany(targetEntity: Role::class, cascade: ['persist'], fetch: 'EAGER')]
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
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * @param Collection<int, Role>|array<int, Role> $roles
     */
    public function setRoles(Collection|array $roles): void
    {
        $this->roles = $roles instanceof Collection
            ? $roles
            : new ArrayCollection($roles);
    }
}
