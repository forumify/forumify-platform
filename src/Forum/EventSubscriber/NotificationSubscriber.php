<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\NotificationService;
use Forumify\Forum\Entity\Subscription;
use Forumify\Forum\Event\CommentCreatedEvent;
use Forumify\Forum\Event\MessageCreatedEvent;
use Forumify\Forum\Event\TopicCreatedEvent;
use Forumify\Forum\Notification\CommentCreatedNotificationType;
use Forumify\Forum\Notification\MessageReplyNotificationType;
use Forumify\Forum\Notification\TopicCreatedNotificationType;
use Forumify\Forum\Repository\SubscriptionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly NotificationService $notificationService,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'sendNotificationsForComment',
            TopicCreatedEvent::class => 'sendNotificationsForTopic',
            MessageCreatedEvent::class => 'sendNotificationsForMessage',
        ];
    }

    public function sendNotificationsForComment(CommentCreatedEvent $createdEvent): void
    {
        $selfIdentifier = $this->security->getUser()?->getUserIdentifier();
        $comment = $createdEvent->getComment();
        $subscriptions = $this->getSubscriptions($comment->getTopic()->getId(), CommentCreatedNotificationType::TYPE);

        foreach ($subscriptions as $subscription) {
            $subscriber = $subscription->getUser();
            if ($subscriber->getUserIdentifier() === $selfIdentifier) {
                continue;
            }

            $this->notificationService->sendNotification(new Notification(
                CommentCreatedNotificationType::TYPE,
                $subscriber,
                ['comment' => $comment]
            ));
        }
    }

    public function sendNotificationsForTopic(TopicCreatedEvent $createdEvent): void
    {
        $selfIdentifier = $this->security->getUser()?->getUserIdentifier();
        $topic = $createdEvent->getTopic();
        $subscriptions = $this->getSubscriptions($topic->getForum()->getId(), TopicCreatedNotificationType::TYPE);

        foreach ($subscriptions as $subscription) {
            $subscriber = $subscription->getUser();
            if ($subscriber->getUserIdentifier() === $selfIdentifier) {
                continue;
            }

            $this->notificationService->sendNotification(new Notification(
                TopicCreatedNotificationType::TYPE,
                $subscriber,
                ['topic' => $topic]
            ));
        }
    }

    /**
     * @return Subscription[]
     */
    private function getSubscriptions(int $subjectId, string $type): array
    {
        return $this->subscriptionRepository->findBy(['subjectId' => $subjectId, 'type' => $type]);
    }

    public function sendNotificationsForMessage(MessageCreatedEvent $event): void
    {
        $message = $event->getMessage();
        $sender = $message->getCreatedBy();
        $participants = $message->getThread()->getParticipants();

        foreach ($participants as $participant) {
            if ($sender === null || $sender->getUserIdentifier() === $participant->getUserIdentifier()) {
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
