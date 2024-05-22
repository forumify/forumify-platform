<?php

declare(strict_types=1);

namespace Forumify\Forum\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\AbstractEmailNotificationType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Topic;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\String\u;

class TopicCreatedNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'topic_created';

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
        $topic = $this->getTopic($notification);
        $author = $topic?->getCreatedBy();
        $forum = $topic?->getForum()?->getTitle();

        return $this->translator->trans('notification.topic_created', [
            'author' => $author?->getDisplayName() ?? 'Deleted',
            'forum' => $forum ?? 'Deleted',
        ]);
    }

    public function getDescription(Notification $notification): string
    {
        /** @var Comment|null|false $firstComment */
        $firstComment = $this->getTopic($notification)?->getComments()?->first();
        if ($firstComment === null || $firstComment === false) {
            return '';
        }

        return u(strip_tags($firstComment->getContent()))
            ->truncate(200, '...', false)
            ->toString();
    }

    public function getImage(Notification $notification): string
    {
        $avatar = $this->getTopic($notification)?->getCreatedBy()?->getAvatar();
        $url = $avatar ?? $this->settingRepository->get('forumify.default_avatar');

        return empty($url)
            ? ''
            : $this->packages->getUrl($url, 'forumify.avatar');
    }

    public function getUrl(Notification $notification): string
    {
        $slug = $this->getTopic($notification)?->getSlug();

        return $slug !== null
            ? $this->urlGenerator->generate('forumify_forum_topic', ['slug' => $slug])
            : '';
    }

    private function getTopic(Notification $notification): ?Topic
    {
        $topic = $notification->getDeserializedContext()['topic'] ?? null;
        if (!$topic instanceof Topic) {
            return null;
        }
        return $topic;
    }

    public function getEmailTemplate(Notification $notification): string
    {
        return '@Forumify/emails/notifications/topic_created.html.twig';
    }
}
