<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use DateTime;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Form\MessageReply;
use Forumify\Forum\Form\NewMessageThread;
use Forumify\Forum\Repository\MessageRepository;
use Forumify\Forum\Repository\MessageThreadRepository;
use Symfony\Bundle\SecurityBundle\Security;

class MessageService
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly Security $security,
        private readonly ReadMarkerRepository $readMarkerRepository,
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
            $participant = $thread->getParticipants()->first() ?: null;
            $thread->setCreatedBy($participant);
        }

        $this->messageThreadRepository->save($thread);

        $reply = new MessageReply();
        $reply->setContent($newThread->getMessage());

        $message = $this->createMessage($thread, $reply);
        $thread->addMessage($message);

        $this->messageThreadRepository->save($thread);
        return $thread;
    }

    public function replyToThread(MessageThread $thread, MessageReply $reply): void
    {
        $message = $this->createMessage($thread, $reply);
        $thread->addMessage($message);

        $this->messageThreadRepository->save($thread);
        $this->readMarkerRepository->unread(MessageThread::class, $thread->getId());
    }

    private function createMessage(MessageThread $thread, MessageReply $reply): Message
    {
        $message = new Message();
        $message->setContent(nl2br($reply->getContent()));
        $message->setThread($thread);
        $message->setCreatedAt(new DateTime());

        if ($this->security->getUser() === null) {
            $participant = $thread->getParticipants()->first() ?: null;
            $message->setCreatedBy($participant);
        }
        return $message;
    }
}
