<?php

declare(strict_types=1);

namespace Forumify\Testing\Factories\Forum;

use Forumify\Forum\Entity\Topic;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Topic>
 */
class TopicFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Topic::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->sentence(),
            'forum' => ForumFactory::createOne(),
        ];
    }
}
