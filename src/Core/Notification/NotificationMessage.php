<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Messenger\AsyncMessageInterface;

class NotificationMessage implements AsyncMessageInterface
{
    public function __construct(private readonly int $notificationId)
    {
    }

    public function getNotificationId(): int
    {
        return $this->notificationId;
    }
}
