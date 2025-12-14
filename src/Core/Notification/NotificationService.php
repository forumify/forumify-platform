<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Repository\NotificationRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @param Notification|array<Notification>
     */
    public function sendNotification(Notification|array $notifications): void
    {
        if (!is_array($notifications)) {
            $notifications = [$notifications];
        }

        $this->notificationRepository->saveAll($notifications);
        $this->messageBus->dispatch(new NotificationMessage(
            array_map(fn (Notification $n) => $n->getId(), $notifications),
        ));
    }
}
