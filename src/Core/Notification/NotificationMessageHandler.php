<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Translation\LocaleSwitcher;

#[AsMessageHandler]
class NotificationMessageHandler
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly NotificationTypeCollection $notificationTypeCollection,
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
    }

    /**
     * @throws NotificationHandlerException
     */
    public function __invoke(NotificationMessage $message): void
    {
        $notification = $this->notificationService->fetchNotification($message->getNotificationId());
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
