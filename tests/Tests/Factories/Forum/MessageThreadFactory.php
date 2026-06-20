<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Forum;

use Forumify\Forum\Entity\MessageThread;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<MessageThread>
 */
class MessageThreadFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return MessageThread::class;
    }

    protected function defaults(): array|callable
    {
        return ['title' => self::faker()->sentence()];
    }
}
