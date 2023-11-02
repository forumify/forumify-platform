<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class NotificationTypeCollection
{
    private array $notificationTypes;

    public function __construct(
        #[TaggedIterator('forumify.notification.type')]
        iterable $notifications,
    ) {
        /** @var NotificationTypeInterface $notification */
        foreach ($notifications as $notification) {
            $this->notificationTypes[$notification->getType()] = $notification;
        }
    }

    public function getNotificationType(string $type): ?NotificationTypeInterface
    {
        return $this->notificationTypes[$type] ?? null;
    }
}
