<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Forumify\Core\Entity\ACL;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Topic;

class CommentRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Comment::class;
    }

    public function findLastCommentInTopic(Topic $topic): ?Comment
    {
        $query = $this
            ->createQueryBuilder('c')
            ->where('c.topic = :topic')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->setParameters(['topic' => $topic])
            ->getQuery();

        try {
            return $query->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
