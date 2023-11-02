<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACL;

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
            'permission' => $permission
        ]);
    }
}
