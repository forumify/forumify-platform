<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\CommentReaction;

/**
 * @extends AbstractRepository<CommentReaction>
 */
class CommentReactionRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return CommentReaction::class;
    }
}
