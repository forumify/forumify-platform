<?php

declare(strict_types=1);

namespace Forumify\Forum\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\AbstractEmailNotificationType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Forum\Entity\Message;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\String\u;

class MessageReplyNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'message_reply';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Packages $packages,
        private readonly SettingRepository $settingRepository,
        MailerInterface $mailer
    ) {
        parent::__construct($mailer);
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTitle(Notification $notification): string
    {
        $sender = $this->getMessage($notification)?->getCreatedBy();
        return $this->translator->trans('notification.message_reply', [
            'sender' => $sender?->getUsername(),
        ]);
    }

    public function getDescription(Notification $notification): string
    {
        $message = u($this->getMessage($notification)->getContent())
            ->truncate(200, '...', false)
            ->toString();

        return $message;
    }

    public function getImage(Notification $notification): string
    {
        $avatar = $this->getMessage($notification)?->getCreatedBy()?->getAvatar();
        $url = $avatar ?? $this->settingRepository->get('forum.default_avatar');

        return $this->packages->getUrl($url, 'forumify.avatar');
    }

    private function getMessage(Notification $notification): ?Message
    {
        $message = $notification->getContext()['message'] ?? null;
        if (!$message instanceof Message) {
            return null;
        }
        return $message;
    }

    public function getEmailTemplate(Notification $notification): string
    {
        return '@Forumify/emails/notifications/message_reply.html.twig';
    }

    protected function shouldSendEmail(Notification $notification): bool
    {
        return $notification
            ->getRecipient()
            ->getNotificationSettings()
            ->isEmailOnMessage();
    }
}
