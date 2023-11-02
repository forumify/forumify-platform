<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Core\Entity\Notification;

class NotificationRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Notification::class;
    }
}
