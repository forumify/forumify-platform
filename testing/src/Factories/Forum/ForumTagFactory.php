<?php

declare(strict_types=1);

namespace Forumify\Testing\Factories\Forum;

use Forumify\Forum\Entity\ForumTag;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ForumTag>
 */
class ForumTagFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ForumTag::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->word(),
            'color' => self::faker()->hexColor(),
        ];
    }
}
