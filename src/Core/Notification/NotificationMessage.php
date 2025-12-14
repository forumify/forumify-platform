<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Messenger\AsyncMessageInterface;

class NotificationMessage implements AsyncMessageInterface
{
    /**
     * @param int|array<int> $notificationId
     */
    public function __construct(
        public readonly int|array $notificationId,
        public readonly bool $ignoreIsOnline = false,
    ) {
    }
}
