<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use DateTime;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;

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
            ->andWhere('t.hidden = 0')
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

    /**
     * Loads comments together with their topic and author.
     *
     * @param array<int> $ids
     * @return array<Comment>
     */
    public function findWithTopicAndAuthor(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->addSelect('t', 'author')
            ->join('c.topic', 't')
            ->leftJoin('c.createdBy', 'author')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<Topic> $topics
     * @return array<int, Comment> keyed by topic id
     */
    public function findLastCommentPerTopic(array $topics): array
    {
        if (empty($topics)) {
            return [];
        }

        $rows = $this->createQueryBuilder('c')
            ->select('IDENTITY(c.topic) AS topicId', 'MAX(c.id) AS lastCommentId')
            ->where('c.topic IN (:topics)')
            ->setParameter('topics', $topics)
            ->groupBy('c.topic')
            ->getQuery()
            ->getScalarResult();

        $topicIdPerComment = [];
        foreach ($rows as $row) {
            $topicIdPerComment[(int)$row['lastCommentId']] = (int)$row['topicId'];
        }

        $lastComments = [];
        foreach ($this->findWithTopicAndAuthor(array_keys($topicIdPerComment)) as $comment) {
            $lastComments[$topicIdPerComment[$comment->getId()]] = $comment;
        }

        return $lastComments;
    }

    /**
     * Resolves the last comment of every given forum in one query.
     *
     * The comment with the highest id is taken as the last one. Comments are only ever appended, so
     * that is the most recent one, without needing a query per forum to order by creation date.
     *
     * @param array<int, TopicVisibility> $visibilityPerForum
     * @return array<int, array{id: int, createdAt: DateTime}> keyed by forum id
     */
    public function findLastCommentPerForum(?User $user, array $visibilityPerForum): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('IDENTITY(t.forum) AS forumId', 'MAX(c.id) AS lastCommentId', 'MAX(c.createdAt) AS lastCommentAt')
            ->join('c.topic', 't')
            ->groupBy('t.forum');

        if (!TopicVisibility::applyTo($qb, 't', $visibilityPerForum, $user)) {
            return [];
        }

        $lastComments = [];
        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $lastComments[(int)$row['forumId']] = [
                'id' => (int)$row['lastCommentId'],
                'createdAt' => new DateTime($row['lastCommentAt']),
            ];
        }

        return $lastComments;
    }
}
