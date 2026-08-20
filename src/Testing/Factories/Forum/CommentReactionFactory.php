<?php

declare(strict_types=1);

namespace Forumify\Testing\Factories\Forum;

use Forumify\Forum\Entity\CommentReaction;
use Forumify\Testing\Factories\Core\UserFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CommentReaction>
 */
class CommentReactionFactory extends PersistentObjectFactory
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
