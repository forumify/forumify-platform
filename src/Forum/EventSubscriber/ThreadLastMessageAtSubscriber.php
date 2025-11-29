<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use DateTime;
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
        $createdAt = $message->getCreatedAt() ?? new DateTime();
        $thread->setLastMessageAt(DateTimeImmutable::createFromMutable($createdAt));

        $this->messageThreadRepository->save($thread);
    }
}
