<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Forum;

use Forumify\Forum\Entity\Comment;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

class CommentFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Comment::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'content' => self::faker()->paragraphs(asText: true),
            'topic' => TopicFactory::createOne(),
        ];
    }
}
