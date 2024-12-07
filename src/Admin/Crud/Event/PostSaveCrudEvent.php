<?php

declare(strict_types=1);

namespace Forumify\Admin\Crud\Event;

/**
 * @template TEntity
 *
 * @extends AbstractEntityCrudEvent<TEntity>
 */
class PostSaveCrudEvent extends AbstractEntityCrudEvent
{
    public static function getName(string $entity): string
    {
        return 'crud.' . $entity . '.post_save';
    }
}
