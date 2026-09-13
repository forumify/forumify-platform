<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Entity\Notification;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.notification.type')]
interface NotificationTypeInterface
{
    public function getType(): string;

    public function getTitle(Notification $notification): string;

    public function getDescription(Notification $notification): string;

    public function getImage(Notification $notification): string;

    public function getUrl(Notification $notification): string;

    /**
     * @throws NotificationHandlerException
     * @throws NotificationSubjectDeletedException the notification will be removed
     */
    public function handleNotification(Notification $notification): void;
}
