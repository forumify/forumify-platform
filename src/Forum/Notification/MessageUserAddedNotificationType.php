<?php

declare(strict_types=1);

namespace Forumify\Forum\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Entity\User;
use Forumify\Core\Notification\AbstractEmailNotificationType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Forum\Entity\MessageThread;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageUserAddedNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'message_user_added';

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
        return $this->translator->trans('notification.message_user_added');
    }

    public function getDescription(Notification $notification): string
    {
        $user = $this->getUser($notification);
        $title = $this->getMessageThread($notification)?->getTitle();

        return $this->translator->trans('notification.message_user_added_desc', [
            'user' => $user?->getDisplayName(),
            'title' => $title,
        ]);
    }

    public function getImage(Notification $notification): string
    {
        $avatar = $this->getUser($notification)?->getAvatar();
        $url = $avatar ?? $this->settingRepository->get('forumify.default_avatar');

        return empty($url)
            ? ''
            : $this->packages->getUrl($url, 'forumify.avatar');
    }

    public function getUrl(Notification $notification): string
    {
        $thread = $this->getMessageThread($notification);
        if ($thread === null) {
            return '';
        }

        return $this->urlGenerator->generate('forumify_forum_messenger_thread', [
            'id' => $thread->getId(),
        ]);
    }

    private function getMessageThread(Notification $notification): ?MessageThread
    {
        $messageThread = $notification->getDeserializedContext()['messageThread'] ?? null;
        if (!$messageThread instanceof MessageThread) {
            return null;
        }
        return $messageThread;
    }

    private function getUser(Notification $notification): ?User
    {
        $user = $notification->getDeserializedContext()['user'] ?? null;
        if (!$user instanceof User) {
            return null;
        }
        return $user;
    }

    public function getEmailTemplate(Notification $notification): string
    {
        return '@Forumify/emails/notifications/message_user_added.html.twig';
    }

    protected function shouldSendEmail(Notification $notification): bool
    {
        return $notification
            ->getRecipient()
            ->getNotificationSettings()
            ->isEmailOnMessage();
    }
}
