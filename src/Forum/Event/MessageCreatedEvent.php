<?php

declare(strict_types=1);

namespace Forumify\Forum\Event;

use Forumify\Forum\Entity\Message;
use Symfony\Contracts\EventDispatcher\Event;

class MessageCreatedEvent extends Event
{
    public function __construct(private readonly Message $message)
    {
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}
