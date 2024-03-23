<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Badge;

class BadgeRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Badge::class;
    }
}
