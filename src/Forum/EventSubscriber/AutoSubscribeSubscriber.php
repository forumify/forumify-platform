<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Forumify\Forum\Event\CommentCreatedEvent;
use Forumify\Forum\Event\TopicCreatedEvent;
use Forumify\Forum\Notification\CommentCreatedNotificationType;
use Forumify\Forum\Service\SubscriptionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class AutoSubscribeSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly SubscriptionService $subService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TopicCreatedEvent::class => 'subscribeOnTopicCreated',
            CommentCreatedEvent::class => 'subscribeOnCommentCreated',
        ];
    }

    public function subscribeOnTopicCreated(TopicCreatedEvent $createdEvent): void
    {
        $user = $createdEvent->getTopic()->getCreatedBy();
        if ($user === null || !$user->getNotificationSettings()->isAutoSubscribeToOwnTopics()) {
            return;
        }

        $this->subService->subscribe(
            $user,
            CommentCreatedNotificationType::TYPE,
            $createdEvent->getTopic()->getId()
        );
    }

    public function subscribeOnCommentCreated(CommentCreatedEvent $createdEvent): void
    {
        $comment = $createdEvent->getComment();
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
