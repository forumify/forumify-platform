<?php

declare(strict_types=1);

namespace Forumify\Testing\Factories\Forum;

use Forumify\Forum\Entity\Message;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Message>
 */
class MessageFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Message::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'thread' => MessageThreadFactory::createOne(),
            'content' => self::faker()->sentence(),
        ];
    }
}
