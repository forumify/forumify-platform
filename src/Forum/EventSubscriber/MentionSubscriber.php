<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Entity\User;
use Forumify\Core\Notification\NotificationService;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Event\CommentCreatedEvent;
use Forumify\Forum\Event\MessageCreatedEvent;
use Forumify\Forum\Notification\MentionNotificationType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MentionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly NotificationService $notificationService,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
            MessageCreatedEvent::class => 'onMessageCreated',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();
        $recipients = $this->getUsersToMention($comment->getContent());

        $notifications = [];
        foreach ($recipients as $recipient) {
            // do not send notification to self
            if ($recipient->getId() === $comment->getCreatedBy()?->getId()) {
                continue;
            }

            $canViewTopic = $this->security->isGrantedForUser($recipient, VoterAttribute::TopicView->value, $comment->getTopic());
            if (!$canViewTopic) {
                continue;
            }

            $notifications[] = new Notification(
                MentionNotificationType::TYPE,
                $recipient,
                ['subject' => $comment],
            );
        }
        $this->notificationService->sendNotification($notifications);
    }

    public function onMessageCreated(MessageCreatedEvent $event): void
    {
        $message = $event->getMessage();
        $recipients = $this->getUsersToMention($message->getContent());

        $messageThreadParticipantIds = $message->getThread()
            ->getParticipants()
            ->map(fn (User $user) => $user->getId())
            ->toArray();

        $notifications = [];
        foreach ($recipients as $recipient) {
            $recipientId = $recipient->getId();
            // do not send notification to self
            if ($recipientId === $message->getCreatedBy()?->getId()) {
                continue;
            }

            // do not send notification if not part of the thread
            if (!in_array($recipientId, $messageThreadParticipantIds, true)) {
                continue;
            }

            $notifications[] = new Notification(
                MentionNotificationType::TYPE,
                $recipient,
                ['subject' => $message],
            );
        }
        $this->notificationService->sendNotification($notifications);
    }

    /**
     * @return array<User>
     */
    private function getUsersToMention(string $richText): array
    {
        $data = new \DOMDocument();
        $data->loadHTML($richText);

        $users = [];
        $spans = $data->getElementsByTagName('span');
        /** @var \DOMElement $span */
        foreach ($spans as $span) {
            if ($span->className !== 'mention') {
                continue;
            }

            $userId = $span->getAttribute('data-id');
            $user = $this->userRepository->find($userId);
            if ($user !== null) {
                $users[] = $user;
            }
        }
        return $users;
    }
}
