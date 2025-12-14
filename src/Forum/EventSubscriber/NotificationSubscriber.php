<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\NotificationService;
use Forumify\Core\Security\VoterAttribute;
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

        $notifications = [];
        foreach ($subscriptions as $subscription) {
            $subscriber = $subscription->getUser();
            if ($subscriber->getUserIdentifier() === $selfIdentifier) {
                continue;
            }

            $canViewComment = $this->security->isGrantedForUser($subscriber, VoterAttribute::TopicView->value, $comment->getTopic());
            if (!$canViewComment) {
                continue;
            }

            $notifications[] = new Notification(
                CommentCreatedNotificationType::TYPE,
                $subscriber,
                ['comment' => $comment]
            );
        }
        $this->notificationService->sendNotification($notifications);
    }

    public function sendNotificationsForTopic(TopicCreatedEvent $createdEvent): void
    {
        $selfIdentifier = $this->security->getUser()?->getUserIdentifier();
        $topic = $createdEvent->getTopic();
        $subscriptions = $this->getSubscriptions($topic->getForum()->getId(), TopicCreatedNotificationType::TYPE);

        $notifications = [];
        foreach ($subscriptions as $subscription) {
            $subscriber = $subscription->getUser();
            if ($subscriber->getUserIdentifier() === $selfIdentifier) {
                continue;
            }

            $canViewTopic = $this->security->isGrantedForUser($subscriber, VoterAttribute::TopicView->value, $topic);
            if (!$canViewTopic) {
                continue;
            }

            $notifications[] = new Notification(
                TopicCreatedNotificationType::TYPE,
                $subscriber,
                ['topic' => $topic]
            );
        }
        $this->notificationService->sendNotification($notifications);
    }

    /**
     * @return array<Subscription>
     */
    private function getSubscriptions(int $subjectId, string $type): array
    {
        return $this->subscriptionRepository->findBy(['subjectId' => $subjectId, 'type' => $type]);
    }

    public function sendNotificationsForMessage(MessageCreatedEvent $event): void
    {
        $message = $event->getMessage();
        $sender = $this->security->getUser();
        $participants = $message->getThread()->getParticipants();

        $notifications = [];
        foreach ($participants as $participant) {
            if ($sender?->getUserIdentifier() === $participant->getUserIdentifier()) {
                continue;
            }

            $notifications[] = new Notification(
                MessageReplyNotificationType::TYPE,
                $participant,
                ['message' => $message]
            );
        }
        $this->notificationService->sendNotification($notifications);
    }
}
