<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Plugin\Entity\Permission;
use Forumify\Plugin\Entity\Plugin;

class PermissionRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Permission::class;
    }

}
