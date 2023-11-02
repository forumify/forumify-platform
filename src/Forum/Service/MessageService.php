<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\NotificationService;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Form\MessageReply;
use Forumify\Forum\Form\NewMessageThread;
use Forumify\Forum\Notification\MessageReplyNotificationType;
use Forumify\Forum\Repository\MessageRepository;
use Forumify\Forum\Repository\MessageThreadRepository;
use Symfony\Bundle\SecurityBundle\Security;

class MessageService
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly NotificationService $notificationService,
        private readonly Security $security,
    ) {
    }

    public function createThread(NewMessageThread $newThread): MessageThread
    {
        $thread = new MessageThread();
        $thread->setTitle($newThread->getTitle());
        $thread->setParticipants($newThread->getParticipants());
        $thread->getParticipants()->add($this->security->getUser());
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

        foreach ($thread->getParticipants() as $participant) {
            if ($message->getCreatedBy()->getUserIdentifier() === $participant->getUserIdentifier()) {
                continue;
            }

            $this->notificationService->sendNotification(new Notification(
                MessageReplyNotificationType::TYPE,
                $participant,
                ['message' => $message]
            ));
        }
    }
}
