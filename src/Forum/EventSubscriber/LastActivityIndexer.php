<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Service\ReindexLastActivityService;

#[AsDoctrineListener(Events::postPersist)]
#[AsDoctrineListener(Events::postRemove)]
class LastActivityIndexer
{
    private const INDEXED_ENTITIES = [
        Forum::class,
        Topic::class,
        Comment::class,
    ];

    public function __construct(
        private readonly ReindexLastActivityService $reindexService
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->reindexIfNeeded($args);
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->reindexIfNeeded($args);
    }

    private function reindexIfNeeded(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $className = get_class($entity);

        if (!in_array($className, static::INDEXED_ENTITIES)) {
            return;
        }

        $this->reindexService->reindexAll();
    }
}
