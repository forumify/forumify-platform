<?php

declare(strict_types=1);

namespace Forumify\Forum\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\AbstractEmailNotificationType;
use Forumify\Forum\Entity\Badge;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewBadgeNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'new_badge';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Packages $packages,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTitle(Notification $notification): string
    {
        return $this->translator->trans('notification.new_badge.title', [
            'badge' => $this->getBadge($notification)?->getName() ?? '',
        ]);
    }

    public function getDescription(Notification $notification): string
    {
        $badge = $this->getBadge($notification);
        return $this->translator->trans('notification.new_badge.description', [
            'badge' => $badge?->getName() ?? '',
        ]);
    }

    public function getImage(Notification $notification): string
    {
        $badge = $this->getBadge($notification);
        return $badge === null ? '' : $this->packages->getUrl($badge->getImage(), 'forumify.asset');
    }

    public function getUrl(Notification $notification): string
    {
        return $this->urlGenerator->generate('forumify_forum_profile', [
            'username' => $notification->getRecipient()->getUsername(),
        ]);
    }

    public function getEmailTemplate(Notification $notification): string
    {
        return '@Forumify/emails/notifications/new_badge.html.twig';
    }

    public function getBadge(Notification $notification): ?Badge
    {
        $badge = $notification->getDeserializedContext()['badge'] ?? null;
        if (!$badge instanceof Badge) {
            return null;
        }
        return $badge;
    }
}
