<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Forumify\Core\Entity\User;
use Forumify\Core\Event\UserBannedEvent;
use Forumify\Forum\Repository\CommentRepository;
use Forumify\Forum\Repository\MessageRepository;
use Forumify\Forum\Repository\MessageThreadRepository;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class DeleteBannedUserContentListener
{
    public function __construct(
        private readonly TopicRepository $topicRepository,
        private readonly CommentRepository $commentRepository,
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly MessageRepository $messageRepository,
    ) {
    }

    public function __invoke(UserBannedEvent $event): void
    {
        if (!$event->deleteContent) {
            return;
        }

        $this->deleteTopics($event->user);
        $this->deleteComments($event->user);
        $this->deleteMessages($event->user);
        $this->deleteMessageThreads($event->user);
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
