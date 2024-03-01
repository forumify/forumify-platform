<?php

declare(strict_types=1);

namespace Forumify\Forum\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\AbstractEmailNotificationType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Forum\Entity\Comment;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\String\u;

class CommentCreatedNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'comment_created';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Packages $packages,
        private readonly SettingRepository $settingRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTitle(Notification $notification): string
    {
        $comment = $this->getComment($notification);
        $author = $comment?->getCreatedBy();
        $topicTitle = $comment?->getTopic()?->getTitle();

        return $this->translator->trans('notification.comment_created', [
            'author' => $author?->getUsername() ?? 'Deleted',
            'topic' => $topicTitle ?? 'Deleted',
        ]);
    }

    public function getDescription(Notification $notification): string
    {
        return u(strip_tags($this->getComment($notification)?->getContent() ?? ''))
            ->truncate(200, '...', false)
            ->toString();
    }

    public function getImage(Notification $notification): string
    {
        $avatar = $this->getComment($notification)?->getCreatedBy()?->getAvatar();
        $url = $avatar ?? $this->settingRepository->get('forum.default_avatar');

        return empty($url)
            ? ''
            : $this->packages->getUrl($url, 'forumify.avatar');
    }

    public function getUrl(Notification $notification): string
    {
        $slug = $this->getComment($notification)?->getTopic()?->getSlug();

        return $slug !== null
            ? $this->urlGenerator->generate('forumify_forum_topic', ['slug' => $slug])
            : '';
    }

    private function getComment(Notification $notification): ?Comment
    {
        $comment = $notification->getDeserializedContext()['comment'] ?? null;
        if (!$comment instanceof Comment) {
            return null;
        }
        return $comment;
    }

    public function getEmailTemplate(Notification $notification): string
    {
        return '@Forumify/emails/notifications/comment_created.html.twig';
    }
}
