<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;

/**
 * @extends AbstractRepository<Comment>
 */
class CommentRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Comment::class;
    }

    /**
     * @return array<Comment>
     */
    public function getUserLastComments(User $user): array
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->join('c.topic', 't')
            ->join('t.forum', 'f')
            ->where('c.createdBy = :user')
            ->setParameter('user', $user)
            ->setMaxResults(10)
            ->orderBy('c.createdAt', 'DESC')
        ;

        $loggedInUser = $this->security->getUser();
        if ($loggedInUser === null) {
            $qb->andWhere('f.displaySettings.onlyShowOwnTopics = 0');
        } elseif (!$this->security->isGranted(VoterAttribute::SuperAdmin->value)) {
            // TODO: This doesn't account for people who have ACL permissions
            // to bypass "show only own topics".
            $qb
                ->andWhere($qb->expr()->orX(
                    'f.displaySettings.onlyShowOwnTopics = 0',
                    't.createdBy = :loggedInUser'
                ))
                ->setParameter('loggedInUser', $loggedInUser)
            ;
        }

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
