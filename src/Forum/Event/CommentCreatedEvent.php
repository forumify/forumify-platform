<?php

declare(strict_types=1);

namespace Forumify\Forum\Event;

use Forumify\Forum\Entity\Comment;
use Symfony\Contracts\EventDispatcher\Event;

class CommentCreatedEvent extends Event
{
    public function __construct(private readonly Comment $comment)
    {
    }

    public function getComment(): Comment
    {
        return $this->comment;
    }
}
