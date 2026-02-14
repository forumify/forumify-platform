<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Forum;

use Forumify\Forum\Entity\Topic;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

class TopicFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Topic::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->sentence(),
        ];
    }
}
