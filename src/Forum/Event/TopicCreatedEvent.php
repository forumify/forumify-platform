<?php

declare(strict_types=1);

namespace Forumify\Forum\Event;

use Forumify\Forum\Entity\Topic;
use Symfony\Contracts\EventDispatcher\Event;

class TopicCreatedEvent extends Event
{
    public function __construct(private readonly Topic $topic)
    {
    }

    public function getTopic(): Topic
    {
        return $this->topic;
    }
}
