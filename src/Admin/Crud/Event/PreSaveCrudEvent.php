<?php

declare(strict_types=1);

namespace Forumify\Admin\Crud\Event;

class PreSaveCrudEvent extends AbstractEntityCrudEvent
{
    public static function getName(string $entity): string
    {
        return 'crud.' . $entity . '.pre_save';
    }
}
