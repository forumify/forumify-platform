<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
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
        $this->getEntityManager()->createQueryBuilder()
            ->update(Topic::class, 't')
            ->set('t.views', $topic->getViews())
            ->where('t.id = :topicId')
            ->setParameter('topicId', $topic->getId())
            ->getQuery()
            ->execute();
    }
}
