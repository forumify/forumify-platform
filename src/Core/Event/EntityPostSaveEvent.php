<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EntityPostSaveEvent extends EntityEvent
{
    public static function getName(string $entity): string
    {
        return 'entity.' . $entity . '.post_save';
    }
}
