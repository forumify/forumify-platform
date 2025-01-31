<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Doctrine\ORM\QueryBuilder;
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
}
