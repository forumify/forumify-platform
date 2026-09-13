<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Entity\User;
use Forumify\Core\Notification\AbstractEmailNotificationType;
use Forumify\Core\Notification\NotificationSubjectDeletedException;
use Forumify\Core\Service\Mailer;
use PHPUnit\Framework\TestCase;

class AbstractEmailNotificationTypeTest extends TestCase
{
    public function testHandleNotificationThrowsWhenSubjectIsDeleted(): void
    {
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects(self::never())->method('send');

        $notificationType = new class extends AbstractEmailNotificationType {
            public function getType(): string
            {
                return 'test';
            }

            public function getTitle(Notification $notification): string
            {
                return '';
            }

            public function getDescription(Notification $notification): string
            {
                return '';
            }

            public function getImage(Notification $notification): string
            {
                return '';
            }

            public function getUrl(Notification $notification): string
            {
                return '';
            }

            public function getEmailTemplate(Notification $notification): string
            {
                return 'irrelevant.html.twig';
            }

            public function isSubjectValid(Notification $notification): bool
            {
                return false;
            }
        };
        $notificationType->setServices($mailer);

        $notification = new Notification('test', new User());
        $notification->setDeserializedContext([]);

        $this->expectException(NotificationSubjectDeletedException::class);
        $notificationType->handleNotification($notification);
    }

    public function testHandleNotificationSendsEmailWhenSubjectIsValid(): void
    {
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects(self::once())->method('send');

        $notificationType = new class extends AbstractEmailNotificationType {
            public function getType(): string
            {
                return 'test';
            }

            public function getTitle(Notification $notification): string
            {
                return 'title';
            }

            public function getDescription(Notification $notification): string
            {
                return '';
            }

            public function getImage(Notification $notification): string
            {
                return '';
            }

            public function getUrl(Notification $notification): string
            {
                return '';
            }

            public function getEmailTemplate(Notification $notification): string
            {
                return 'irrelevant.html.twig';
            }
        };
        $notificationType->setServices($mailer);

        $notification = new Notification('test', new User());
        $notification->setDeserializedContext([]);

        $notificationType->handleNotification($notification);
    }
}
