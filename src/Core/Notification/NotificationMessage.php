<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

class NotificationMessage
{
    public function __construct(private readonly int $notificationId)
    {
    }

    public function getNotificationId(): int
    {
        return $this->notificationId;
    }
}
