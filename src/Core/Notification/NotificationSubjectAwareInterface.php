<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Entity\Notification;

interface NotificationSubjectAwareInterface
{
    public function isSubjectValid(Notification $notification): bool;
}
