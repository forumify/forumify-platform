<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Forumify\Core\Entity\SortableEntityInterface;
use Forumify\Core\Repository\AbstractRepository;

#[AsDoctrineListener(event: Events::prePersist)]
class SortablePositionListener
{
    public function prePersist(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof SortableEntityInterface) {
            return;
        }

        $repository = $event->getObjectManager()->getRepository($entity::class);
        if (!$repository instanceof AbstractRepository) {
            return;
        }

        $highestPosition = $repository->getHighestPosition($entity);
        $entity->setPosition($highestPosition + 1);
    }
}
