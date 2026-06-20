<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Forum;

use Forumify\Forum\Entity\ForumGroup;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ForumGroup>
 */
class ForumGroupFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ForumGroup::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->sentence(3),
        ];
    }
}
