<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\NotificationService;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\Subscription;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Notification\CommentCreatedNotificationType;
use Forumify\Forum\Notification\MessageReplyNotificationType;
use Forumify\Forum\Notification\TopicCreatedNotificationType;
use Forumify\Forum\Repository\SubscriptionRepository;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::postPersist, method: 'sendNotificationsForComment', entity: Comment::class)]
#[AsEntityListener(event: Events::postPersist, method: 'sendNotificationsForTopic', entity: Topic::class)]
#[AsEntityListener(event: Events::postPersist, method: 'sendNotificationsForMessage', entity: Message::class)]
class NotificationListener
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly NotificationService $notificationService,
        private readonly Security $security,
    ) {
    }

    public function sendNotificationsForComment(Comment $comment): void
    {
        $selfIdentifier = $this->security->getUser()?->getUserIdentifier();
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

    public function sendNotificationsForTopic(Topic $topic): void
    {
        $selfIdentifier = $this->security->getUser()?->getUserIdentifier();
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

    public function sendNotificationsForMessage(Message $message): void
    {
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
