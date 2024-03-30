<?php

declare(strict_types=1);

namespace Forumify\Cms\Repository;

use Forumify\Cms\Entity\Resource;
use Forumify\Core\Repository\AbstractRepository;

class ResourceRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Resource::class;
    }
}
