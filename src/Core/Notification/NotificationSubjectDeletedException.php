<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Throwable;

class NotificationSubjectDeletedException extends NotificationHandlerException
{
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message ?: 'Notification subject has been deleted.', $code, $previous);
    }
}
