<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Event\MessageCreatedEvent;
use Forumify\Forum\Form\MessageReply;
use Forumify\Forum\Form\NewMessageThread;
use Forumify\Forum\Repository\MessageRepository;
use Forumify\Forum\Repository\MessageThreadRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MessageService
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly Security $security,
        private readonly ReadMarkerRepository $readMarkerRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createThread(NewMessageThread $newThread): MessageThread
    {
        $thread = new MessageThread();
        $thread->setTitle($newThread->getTitle());
        $thread->setParticipants($newThread->getParticipants());

        /** @var User|null $user */
        $user = $this->security->getUser();
        if ($user !== null) {
            $thread->getParticipants()->add($user);
        } else {
            $thread->setCreatedBy($thread->getParticipants()->first());
        }

        $this->messageThreadRepository->save($thread);

        $reply = new MessageReply();
        $reply->setContent($newThread->getMessage());
        $this->replyToThread($thread, $reply);

        $this->messageThreadRepository->save($thread);
        return $thread;
    }

    public function replyToThread(MessageThread $thread, MessageReply $reply): void
    {
        $message = new Message();
        $message->setContent(nl2br($reply->getContent()));
        $message->setThread($thread);

        if ($this->security->getUser() === null) {
            $message->setCreatedBy($thread->getParticipants()->first());
        }

        $this->messageRepository->save($message);
        $this->readMarkerRepository->unread(MessageThread::class, $thread->getId());

        $this->eventDispatcher->dispatch(new MessageCreatedEvent($message));
    }
}
