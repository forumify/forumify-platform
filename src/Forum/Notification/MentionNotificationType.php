<?php

declare(strict_types=1);

namespace Forumify\Forum\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Entity\User;
use Forumify\Core\Notification\AbstractEmailNotificationType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Message;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\String\u;

class MentionNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'mention';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Packages $packages,
        private readonly SettingRepository $settingRepository,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTitle(Notification $notification): string
    {
        $subject = $this->getSubject($notification);
        if ($subject === null) {
            return '';
        }

        return match (get_class($subject)) {
            Comment::class => $this->translator->trans('notification.mention_comment', [
                'author' => $subject->getCreatedBy()?->getDisplayName()
            ]),
            Message::class => $this->translator->trans('notification.mention_message', [
                'author' => $subject->getCreatedBy()?->getDisplayName()
            ]),
            default => $this->translator->trans('notification.mention_generic'),
        };
    }

    public function getDescription(Notification $notification): string
    {
        $subject = $this->getSubject($notification);
        $sender = $subject?->getCreatedBy()?->getDisplayName() ?? 'Deleted';
        $content = $subject?->getContent() ?? '';

        return $sender . ': ' . u(strip_tags($content))
                ->truncate(200, '...', false)
                ->toString();
    }

    public function getImage(Notification $notification): string
    {
        $subject = $this->getSubject($notification);
        if ($subject === null) {
            return '';
        }

        $author = match (get_class($subject)) {
            Comment::class, Message::class => $subject->getCreatedBy(),
            default => null,
        };

        $url = $author?->getAvatar() ?? $this->settingRepository->get('forumify.default_avatar');
        return empty($url)
            ? ''
            : $this->packages->getUrl($url, 'forumify.avatar');

    }

    public function getUrl(Notification $notification): string
    {
        $subject = $this->getSubject($notification);
        if ($subject === null) {
            return '';
        }

        return match (get_class($subject)) {
            Comment::class => $this->urlGenerator->generate('forumify_forum_topic', ['slug' => $subject->getTopic()->getSlug()]),
            Message::class => $this->urlGenerator->generate('forumify_forum_messenger_thread', ['id' => $subject->getThread()->getId()]),
            default => '',
        };
    }

    public function getEmailTemplate(Notification $notification): string
    {
        return '@Forumify/emails/notifications/mention.html.twig';
    }

    private function getSubject(Notification $notification): mixed
    {
        return $notification->getDeserializedContext()['subject'] ?? null;
    }
}
