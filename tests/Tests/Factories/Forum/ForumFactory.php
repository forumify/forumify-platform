<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Forum;

use Forumify\Forum\Entity\Forum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

class ForumFactory extends PersistentProxyObjectFactory
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
