<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACL;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;

/**
 * @extends AbstractRepository<ACL>
 */
class ACLRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return ACL::class;
    }

    public function findByEntity(AccessControlledEntityInterface $entity): array
    {
        $aclParameters = $entity->getACLParameters();

        return $this->findBy([
            'entity' => $aclParameters->entity,
            'entityId' => $aclParameters->entityId,
        ]);
    }

    public function findOneByEntityAndPermission(AccessControlledEntityInterface $entity, string $permission): ?ACL
    {
        $aclParameters = $entity->getACLParameters();

        return $this->findOneBy([
            'entity' => $aclParameters->entity,
            'entityId' => $aclParameters->entityId,
            'permission' => $permission,
        ]);
    }

    /**
     * @param User|null $user
     * @return array<array{ entity: string, entityId: string, permission: string }>
     */
    public function findByUser(?User $user): array
    {
        if ($user === null) {
            $slugs = ['guest'];
        } else {
            $slugs = $user
                ->getRoleEntities()
                ->map(fn (Role $role) => $role->getSlug())
                ->toArray();
            $slugs[] = 'user';
        }

        return $this->createQueryBuilder('acl')
            ->select('acl.entity', 'acl.entityId', 'acl.permission')
            ->join('acl.roles', 'r')
            ->where('r.slug IN (:roleSlugs)')
            ->setParameter('roleSlugs', $slugs)
            ->getQuery()
            ->getResult();
    }
}
