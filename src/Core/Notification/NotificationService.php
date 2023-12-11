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

    public function sendNotification(Notification $notification): void
    {
        $this->notificationRepository->save($notification);
        $this->messageBus->dispatch(new NotificationMessage($notification->getId()));
    }
}
