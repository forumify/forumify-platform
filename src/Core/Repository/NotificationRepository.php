<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\ContextSerializer;

/**
 * @extends AbstractRepository<Notification>
 */
class NotificationRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ContextSerializer $contextSerializer
    ) {
        parent::__construct($registry);
    }

    public static function getEntityClass(): string
    {
        return Notification::class;
    }

    public function find(mixed $id, LockMode|int|null $lockMode = null, int|null $lockVersion = null): ?Notification
    {
        /** @var Notification|null $notification */
        $notification = parent::find($id, $lockMode, $lockVersion);
        if ($notification === null) {
            return null;
        }

        $this->deserializeContext($notification);
        return $notification;
    }

    /**
     * @return array<Notification>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        /** @var array<Notification> $notifications */
        $notifications = parent::findBy($criteria, $orderBy, $limit, $offset);
        foreach ($notifications as $notification) {
            $this->deserializeContext($notification);
        }

        return $notifications;
    }

    private function deserializeContext(Notification $notification): void
    {
        $deserializedContext = $this->contextSerializer->deserialize($notification->getContext());
        $notification->setDeserializedContext($deserializedContext);
    }

    public function save(object $entity, bool $flush = true): void
    {
        $serializedContext = $this->contextSerializer->serialize($entity->getContext());
        $entity->setContext($serializedContext);
        parent::save($entity, $flush);
    }
}
