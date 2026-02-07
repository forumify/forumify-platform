<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Entity\Topic;

#[AsEntityListener(event: Events::postPersist, method: 'clearCommentMarker', entity: Comment::class)]
#[AsEntityListener(event: Events::postPersist, method: 'clearMessageMarker', entity: Message::class)]
class ClearReadMarkerListener
{
    public function __construct(
        private readonly ReadMarkerRepository $readMarkerRepository,
    ) {
    }

    public function clearCommentMarker(Comment $comment): void
    {
        $this->readMarkerRepository->unread(Topic::class, $comment->getTopic()->getId());
    }

    public function clearMessageMarker(Message $message): void
    {
        $this->readMarkerRepository->unread(MessageThread::class, $message->getThread()->getId());
    }
}
