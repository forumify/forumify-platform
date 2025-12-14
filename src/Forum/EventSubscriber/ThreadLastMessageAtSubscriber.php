<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Repository\MessageThreadRepository;

#[AsEntityListener(Events::postPersist, 'setThreadLastMessageAt', entity: Message::class)]
class ThreadLastMessageAtSubscriber
{
    public function __construct(private readonly MessageThreadRepository $messageThreadRepository)
    {
    }

    public function setThreadLastMessageAt(Message $message): void
    {
        $thread = $message->getThread();
        $thread->setLastMessageAt(DateTimeImmutable::createFromMutable(
            $message->getCreatedAt(),
        ));

        $this->messageThreadRepository->save($thread);
    }
}
