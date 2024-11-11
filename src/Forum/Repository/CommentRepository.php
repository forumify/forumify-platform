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
}
