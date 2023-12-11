<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\NotificationContextSerializer;

class NotificationRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly NotificationContextSerializer $contextSerializer
    ) {
        parent::__construct($registry);
    }

    public static function getEntityClass(): string
    {
        return Notification::class;
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?Notification
    {
        /** @var Notification|null $notification */
        $notification = parent::find($id, $lockMode, $lockVersion);
        if ($notification === null) {
            return null;
        }

        $deserializedContext = $this->contextSerializer->deserialize($notification->getContext());
        $notification->setContext($deserializedContext);

        return $notification;
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        /** @var array<Notification> $notifications */
        $notifications = parent::findBy($criteria, $orderBy, $limit, $offset);
        foreach ($notifications as $notification) {
            $deserializedContext = $this->contextSerializer->deserialize($notification->getContext());
            $notification->setContext($deserializedContext);
        }

        return $notifications;
    }

    public function save(object $entity, bool $flush = true): void
    {
        if ($entity instanceof Notification) {
            $serializedContext = $this->contextSerializer->serialize($entity->getContext());
            $entity->setContext($serializedContext);
        }

        parent::save($entity, $flush);
    }
}
