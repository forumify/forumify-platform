<?php

declare(strict_types=1);

namespace Forumify\Forum\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\AbstractEmailNotificationType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageReplyNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'message_reply';

    public function __construct(
        private readonly TranslatorInterface $translator,
        MailerInterface $mailer
    ) {
        parent::__construct($mailer);
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTemplate(): string
    {
        return '@Forumify/emails/notifications/message_reply.html.twig';
    }

    public function getSubject(): string
    {
        return $this->translator->trans('notification.message_reply');
    }

    protected function shouldSendEmail(Notification $notification): bool
    {
        return $notification
            ->getRecipient()
            ->getNotificationSettings()
            ->isEmailOnMessage();
    }
}
