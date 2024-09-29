<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

class EntityPostRemoveEvent extends EntityEvent
{
    public static function getName(string $entity): string
    {
        return 'entity.' . $entity . '.post_remove';
    }
}
