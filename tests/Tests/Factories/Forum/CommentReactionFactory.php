<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Forum;

use Forumify\Forum\Entity\CommentReaction;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Tests\Tests\Factories\Core\UserFactory;

class CommentReactionFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return CommentReaction::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'comment' => CommentFactory::createOne(),
            'user' => UserFactory::createOne(),
            'reaction' => ReactionFactory::createOne(),
        ];
    }
}
