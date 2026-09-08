<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Comment;
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

    /**
     * @param array<Comment> $comments
     * @return array<int, int> reaction counts keyed by comment id
     */
    public function countPerComment(array $comments): array
    {
        if (empty($comments)) {
            return [];
        }

        $rows = $this->createQueryBuilder('cr')
            ->select('IDENTITY(cr.comment) AS commentId', 'COUNT(cr.id) AS reactionCount')
            ->where('cr.comment IN (:comments)')
            ->setParameter('comments', $comments)
            ->groupBy('cr.comment')
            ->getQuery()
            ->getScalarResult();

        $counts = array_fill_keys(array_map(static fn (Comment $comment) => $comment->getId(), $comments), 0);

        return array_replace($counts, array_map(intval(...), array_column($rows, 'reactionCount', 'commentId')));
    }
}
