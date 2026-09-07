<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Entity\User;
use Forumify\Core\Notification\NotificationMessage;
use Forumify\Core\Notification\NotificationMessageHandler;
use Forumify\Core\Notification\NotificationSubjectDeletedException;
use Forumify\Core\Notification\NotificationTypeCollection;
use Forumify\Core\Notification\NotificationTypeInterface;
use Forumify\Core\Repository\NotificationRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\LocaleSwitcher;

class NotificationMessageHandlerTest extends TestCase
{
    public function testNotificationIsRemovedWhenSubjectHasBeenDeleted(): void
    {
        $notification = new Notification('test', new User());

        $notificationRepository = $this->createMock(NotificationRepository::class);
        $notificationRepository->method('find')->willReturn($notification);
        $notificationRepository->expects(self::once())->method('remove')->with($notification);

        $notificationType = $this->createMock(NotificationTypeInterface::class);
        $notificationType->method('handleNotification')->willThrowException(
            new NotificationSubjectDeletedException(),
        );

        $notificationTypeCollection = $this->createMock(NotificationTypeCollection::class);
        $notificationTypeCollection->method('getNotificationType')->willReturn($notificationType);

        $handler = new NotificationMessageHandler(
            $notificationRepository,
            $notificationTypeCollection,
            $this->createRunningLocaleSwitcher(),
            $this->createMock(MessageBusInterface::class),
        );

        $handler(new NotificationMessage(1));
    }

    public function testNotificationIsNotRemovedWhenHandledSuccessfully(): void
    {
        $notification = new Notification('test', new User());

        $notificationRepository = $this->createMock(NotificationRepository::class);
        $notificationRepository->method('find')->willReturn($notification);
        $notificationRepository->expects(self::never())->method('remove');

        $notificationType = $this->createMock(NotificationTypeInterface::class);
        $notificationType->expects(self::once())->method('handleNotification');

        $notificationTypeCollection = $this->createMock(NotificationTypeCollection::class);
        $notificationTypeCollection->method('getNotificationType')->willReturn($notificationType);

        $handler = new NotificationMessageHandler(
            $notificationRepository,
            $notificationTypeCollection,
            $this->createRunningLocaleSwitcher(),
            $this->createMock(MessageBusInterface::class),
        );

        $handler(new NotificationMessage(1));
    }

    private function createRunningLocaleSwitcher(): LocaleSwitcher
    {
        $localeSwitcher = $this->createMock(LocaleSwitcher::class);
        $localeSwitcher->method('runWithLocale')->willReturnCallback(
            fn (string $locale, callable $callback) => $callback(),
        );
        return $localeSwitcher;
    }
}
