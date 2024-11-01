<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

class EntityPostSaveEvent extends EntityEvent
{
    public static function getName(string $entity): string
    {
        return 'entity.' . $entity . '.post_save';
    }
}
