<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

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
}
