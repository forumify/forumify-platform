<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Exception;
use Forumify\Core\Entity\Role;

/**
 * @extends AbstractRepository<Role>
 */
class RoleRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Role::class;
    }

    public function getHighestPosition(object $entity): int
    {
        try {
            return (int) $this
                ->createQueryBuilder('e')
                ->select('MAX(e.position)')
                ->where('e.system = 0')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        } catch (Exception) {
            return 0;
        }
    }
}
