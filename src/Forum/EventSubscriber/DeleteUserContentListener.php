<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Forumify\Core\Entity\User;
use Forumify\Core\Event\UserBannedEvent;
use Forumify\Core\Event\UserDeletedEvent;
use Forumify\Forum\Repository\CommentRepository;
use Forumify\Forum\Repository\MessageRepository;
use Forumify\Forum\Repository\MessageThreadRepository;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserBannedEvent::class, method: 'onUserBanned')]
#[AsEventListener(event: UserDeletedEvent::class, method: 'onUserDeleted')]
class DeleteUserContentListener
{
    public function __construct(
        private readonly TopicRepository $topicRepository,
        private readonly CommentRepository $commentRepository,
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly MessageRepository $messageRepository,
    ) {
    }

    public function onUserBanned(UserBannedEvent $event): void
    {
        if ($event->deleteContent) {
            $this->deleteContent($event->user);
        }
    }

    public function onUserDeleted(UserDeletedEvent $event): void
    {
        if ($event->deleteContent) {
            $this->deleteContent($event->user);
        }
    }

    private function deleteContent(User $user): void
    {
        $this->deleteTopics($user);
        $this->deleteComments($user);
        $this->deleteMessages($user);
        $this->deleteMessageThreads($user);
    }

    private function deleteTopics(User $user): void
    {
        $this->topicRepository->removeAll($this->topicRepository->findBy(['createdBy' => $user]));
    }

    private function deleteComments(User $user): void
    {
        $this->commentRepository->removeAll($this->commentRepository->findBy(['createdBy' => $user]));
    }

    private function deleteMessages(User $user): void
    {
        $this->messageRepository->removeAll($this->messageRepository->findBy(['createdBy' => $user]));
    }

    private function deleteMessageThreads(User $user): void
    {
        $this->messageThreadRepository->removeAll($this->messageThreadRepository->findBy(['createdBy' => $user]));
    }
}
