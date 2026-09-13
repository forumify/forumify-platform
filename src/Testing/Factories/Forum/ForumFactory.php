<?php

declare(strict_types=1);

namespace Forumify\Testing\Factories\Forum;

use Forumify\Forum\Entity\Forum;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Forum>
 */
class ForumFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Forum::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->title(),
        ];
    }
}
