<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Entity\User;
use Forumify\Core\Event\EntityPostSaveEvent;
use Forumify\Core\Notification\NotificationService;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Security\Voter\AccessControlListVoter;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Event\CommentCreatedEvent;
use Forumify\Forum\Notification\MentionNotificationType;
use Forumify\Forum\Security\UserToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MentionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly NotificationService $notificationService,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
            EntityPostSaveEvent::getName(Message::class) => 'onMessageCreated',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();
        $recipients = $this->getUsersToMention($comment->getContent());

        foreach ($recipients as $recipient) {
            if ($recipient->getId() === $comment->getCreatedBy()?->getId()) {
                continue;
            }

            $canViewTopic = $this->accessDecisionManager->decide(
                new UserToken($recipient),
                [VoterAttribute::ACL->value],
                [
                    'permission' => 'view',
                    'entity' => $comment->getTopic()->getForum(),
                ],
            );

            if (!$canViewTopic) {
                continue;
            }

            $this->notificationService->sendNotification(new Notification(
                MentionNotificationType::TYPE,
                $recipient,
                ['subject' => $comment],
            ));
        }
    }

    public function onMessageCreated(EntityPostSaveEvent $event): void
    {
        /** @var Message $message */
        $message = $event->getEntity();
        $recipients = $this->getUsersToMention($message->getContent());

        $messageThreadParticipantIds = $message->getThread()
            ->getParticipants()
            ->map(fn (User $user) => $user->getId())
            ->toArray();

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

            $this->notificationService->sendNotification(new Notification(
                MentionNotificationType::TYPE,
                $recipient,
                ['subject' => $message],
            ));
        }
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
