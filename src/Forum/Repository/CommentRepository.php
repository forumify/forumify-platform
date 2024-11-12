<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
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

    public function getUserLastComments(User $user): array
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->join('c.topic', 't')
            ->join('t.forum', 'f')
            ->where('c.createdBy = :user')
            ->setParameter('user', $user)
            ->setMaxResults(10)
            ->orderBy('c.createdAt', 'DESC');

        $this->addACLToQuery($qb, 'view', Forum::class, 'f');

        return $qb->getQuery()->getResult();
    }

    public function findLastCommentForForumAndUserId(
        Forum $forum,
        string $userId,
        bool $canViewAll,
        bool $canViewHidden,
    ): ?Comment {
        $qb = $this->createQueryBuilder('c')
            ->join('c.topic', 't')
            ->innerJoin('t.forum', 'f', 'WITH', 'f.id = :forumId')
            ->setParameter('forumId', $forum->getId())
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1);

        if (!$canViewHidden) {
            $qb->andWhere('t.hidden = 0');
        }

        if (!$canViewAll) {
            $qb->join('t.createdBy', 'author')
                ->andWhere('author.id = :userId')
                ->setParameter('userId', $userId);
        }

        $this->addACLToQuery($qb, 'view', Forum::class, 'f');

        try {
            return $qb
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
