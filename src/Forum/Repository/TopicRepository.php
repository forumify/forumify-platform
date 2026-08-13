<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Entity\ReadMarker;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;

/**
 * @extends AbstractRepository<Topic>
 */
class TopicRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Topic::class;
    }

    public function incrementViews(Topic $topic): void
    {
        $topic->setViews($topic->getViews() + 1);
        $this->createQueryBuilder('t')
            ->update(Topic::class, 't')
            ->set('t.views', $topic->getViews())
            ->where('t.id = :topicId')
            ->setParameter('topicId', $topic->getId())
            ->getQuery()
            ->execute();
    }

    public function getVisibleTopicsQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.forum', 'f')
        ;

        $this->addACLToQuery($qb, 'view', Forum::class, 'f');

        return $qb;
    }

    /**
     * @param array<int, TopicVisibility> $visibilityPerForum
     * @return array<int> ids of the forums containing at least one topic the user has not read yet
     */
    public function findForumIdsWithUnreadTopics(User $user, array $visibilityPerForum): array
    {
        $qb = $this->createVisibleTopicsQuery($user, $visibilityPerForum);
        if ($qb === null) {
            return [];
        }

        $qb
            ->select('IDENTITY(t.forum)')
            ->distinct()
            ->leftJoin(
                ReadMarker::class,
                'rm',
                Join::WITH,
                'rm.user = :readMarkerUser AND rm.subject = :readMarkerSubject AND rm.subjectId = t.id'
            )
            ->andWhere('rm.subjectId IS NULL')
            ->setParameter('readMarkerUser', $user)
            ->setParameter('readMarkerSubject', Topic::class)
        ;

        return array_map(intval(...), $qb->getQuery()->getSingleColumnResult());
    }

    /**
     * @param array<int, TopicVisibility> $visibilityPerForum
     * @return array<int>
     */
    public function findVisibleTopicIds(User $user, array $visibilityPerForum): array
    {
        $qb = $this->createVisibleTopicsQuery($user, $visibilityPerForum);
        if ($qb === null) {
            return [];
        }

        return array_map(intval(...), $qb->select('t.id')->getQuery()->getSingleColumnResult());
    }

    /**
     * @param array<int, TopicVisibility> $visibilityPerForum
     * @return QueryBuilder|null null when there is nothing to select
     */
    private function createVisibleTopicsQuery(User $user, array $visibilityPerForum): ?QueryBuilder
    {
        $qb = $this->createQueryBuilder('t');

        return TopicVisibility::applyTo($qb, 't', $visibilityPerForum, $user)
            ? $qb
            : null;
    }
}
