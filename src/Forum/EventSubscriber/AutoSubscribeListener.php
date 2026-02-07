<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Notification\CommentCreatedNotificationType;
use Forumify\Forum\Service\SubscriptionService;

#[AsEntityListener(event: Events::postPersist, method: 'subscribeOnTopicCreated', entity: Topic::class)]
#[AsEntityListener(event: Events::postPersist, method: 'subscribeOnCommentCreated', entity: Comment::class)]
class AutoSubscribeListener
{
    public function __construct(private readonly SubscriptionService $subService)
    {
    }

    public function subscribeOnTopicCreated(Topic $topic): void
    {
        $user = $topic->getCreatedBy();
        if ($user === null || !$user->getNotificationSettings()->isAutoSubscribeToOwnTopics()) {
            return;
        }

        $this->subService->subscribe(
            $user,
            CommentCreatedNotificationType::TYPE,
            $topic->getId()
        );
    }

    public function subscribeOnCommentCreated(Comment $comment): void
    {
        if ($comment->getTopic()->getComments()->isEmpty()) {
            // first comment is already handled by subscribeOnTopicCreated
            return;
        }

        $user = $comment->getCreatedBy();
        if ($user === null || !$user->getNotificationSettings()->isAutoSubscribeToTopics()) {
            return;
        }

        $this->subService->subscribe(
            $user,
            CommentCreatedNotificationType::TYPE,
            $comment->getTopic()->getId()
        );
    }
}
