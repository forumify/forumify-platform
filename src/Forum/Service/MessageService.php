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
        /** @var User $user */
        $user = $this->security->getUser();

        $thread = new MessageThread();
        $thread->setTitle($newThread->getTitle());
        $thread->setParticipants($newThread->getParticipants());
        $thread->getParticipants()->add($user);
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
        $this->messageRepository->save($message);
        $this->readMarkerRepository->unread(MessageThread::class, $thread->getId());

        $this->eventDispatcher->dispatch(new MessageCreatedEvent($message));
    }
}
