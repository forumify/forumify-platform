<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Subscription;

class SubscriptionRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Subscription::class;
    }
}
