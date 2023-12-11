<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\NotificationTypeCollection;
use Forumify\Core\Notification\NotificationTypeInterface;
use Forumify\Core\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Notifications', '@Forumify/frontend/components/notifications.html.twig')]
class Notifications extends AbstractController
{
    use DefaultActionTrait;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly NotificationTypeCollection $notificationTypeCollection
    ) {
    }

    public function getNotifications(): array
    {
        return $this->notificationRepository->findBy(
            ['recipient' => $this->getUser()],
            ['seen' => 'ASC', 'createdAt' => 'DESC'],
            10
        );
    }

    public function getUnseenCount(): int
    {
        return $this->notificationRepository->count([
            'seen' => false,
            'recipient' => $this->getUser(),
        ]);
    }

    public function getNotificationType(Notification $notification): NotificationTypeInterface
    {
        return $this->notificationTypeCollection->getNotificationType($notification->getType());
    }
}
