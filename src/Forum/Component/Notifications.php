<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\NotificationTypeCollection;
use Forumify\Core\Notification\NotificationTypeInterface;
use Forumify\Core\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Notifications', '@Forumify/frontend/components/notifications.html.twig', csrf: false)]
class Notifications extends AbstractController
{
    use DefaultActionTrait;

    /** @var array<Notification>|null  */
    private ?array $notifications = null;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly NotificationTypeCollection $notificationTypeCollection
    ) {
    }

    #[LiveAction]
    public function markAsRead(): void
    {
        $notifications = $this->getNotifications();
        foreach ($notifications as $notification) {
            $notification->setSeen(true);
        }
        $this->notificationRepository->saveAll($notifications);
    }

    /**
     * @return array<Notification>
     */
    public function getNotifications(): array
    {
        if ($this->notifications !== null) {
            return $this->notifications;
        }

        $this->notifications = $this->notificationRepository->findBy(
            ['recipient' => $this->getUser()],
            ['seen' => 'ASC', 'createdAt' => 'DESC'],
            10
        );
        return $this->notifications;
    }

    public function getUnseenCount(): int
    {
        return $this->notificationRepository->count([
            'seen' => false,
            'recipient' => $this->getUser(),
        ]);
    }

    public function getNotificationType(Notification $notification): ?NotificationTypeInterface
    {
        return $this->notificationTypeCollection->getNotificationType($notification->getType());
    }
}
