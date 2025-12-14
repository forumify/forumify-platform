<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Exception;
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

    public function __invoke(NotificationMessage $message): void
    {
        $notificationIds = is_array($message->notificationId)
            ? $message->notificationId
            : [$message->notificationId];

        $retryLater = [];
        foreach ($notificationIds as $id) {
            $this->sendNotification($id, $message->ignoreIsOnline, $retryLater);
        }

        if (!empty($retryLater)) {
            // Users are online but have not seen the notification yet, retry later.
            $this->messageBus->dispatch(new NotificationMessage($retryLater, true), [
                new DelayStamp(600_000),
            ]);
        }
    }

    /**
     * @param array<int> $retryLater
     * @param-out array<int> $retryLater
     */
    private function sendNotification(int $notificationId, bool $ignoreIsOnline, array &$retryLater): void
    {
        /** @var Notification|null $notification */
        $notification = $this->notificationRepository->find($notificationId);
        if ($notification === null || $notification->isSeen()) {
            return;
        }

        $notificationType = $this->notificationTypeCollection->getNotificationType($notification->getType());
        if ($notificationType === null) {
            return;
        }

        if (!$ignoreIsOnline && $notification->getRecipient()->isOnline()) {
            $retryLater[] = $notification->getId();
            return;
        }

        $language = $notification->getRecipient()->getLanguage();
        $this->localeSwitcher->runWithLocale($language, function () use ($notificationType, $notification) {
            try {
                $notificationType->handleNotification($notification);
            } catch (Exception) {
            }
        });
    }
}
