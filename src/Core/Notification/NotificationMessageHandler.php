<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Repository\NotificationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Translation\LocaleSwitcher;

#[AsMessageHandler]
class NotificationMessageHandler
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly NotificationTypeCollection $notificationTypeCollection,
        private readonly LocaleSwitcher $localeSwitcher,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws NotificationHandlerException
     */
    public function __invoke(NotificationMessage $message): void
    {
        /** @var Notification|null $notification */
        $notification = $this->notificationRepository->find($message->notificationId);
        if ($notification === null || $notification->isSeen()) {
            return;
        }

        $notificationType = $this->notificationTypeCollection->getNotificationType($notification->getType());
        if ($notificationType === null) {
            throw new NotificationHandlerException("Unable to handle notification of type '{$notification->getType()}'.");
        }

        if (!$message->ignoreIsOnline && $notification->getRecipient()->isOnline()) {
            // The user is online, delay the notification by 10 minutes, if they haven't seen it by then, send it.
            $this->messageBus->dispatch(new NotificationMessage($notification->getId(), true), [
                new DelayStamp(600_000),
            ]);
            return;
        }

        $language = $notification->getRecipient()->getLanguage();
        $this->localeSwitcher->runWithLocale($language, function () use ($notificationType, $notification) {
            $notificationType->handleNotification($notification);
        });
    }
}
