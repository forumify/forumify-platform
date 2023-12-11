<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Repository\NotificationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Translation\LocaleSwitcher;

#[AsMessageHandler]
class NotificationMessageHandler
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly NotificationTypeCollection $notificationTypeCollection,
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
    }

    /**
     * @throws NotificationHandlerException
     */
    public function __invoke(NotificationMessage $message): void
    {
        $notification = $this->notificationRepository->find($message->getNotificationId());
        if ($notification === null) {
            throw new NotificationHandlerException("Unable to find notification with id '{$message->getNotificationId()}'.");
        }

        $notificationType = $this->notificationTypeCollection->getNotificationType($notification->getType());
        if ($notificationType === null) {
            throw new NotificationHandlerException("Unable to handle notification of type '{$notification->getType()}'.");
        }

        $language = $notification->getRecipient()->getLanguage();
        $this->localeSwitcher->runWithLocale($language, function () use ($notificationType, $notification) {
            $notificationType->handleNotification($notification);
        });
    }
}
