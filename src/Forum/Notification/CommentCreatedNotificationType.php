<?php

declare(strict_types=1);

namespace Forumify\Forum\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\AbstractEmailNotificationType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentCreatedNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'comment_created';

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

    public function getTitle(Notification $notification): string
    {
        return $this->translator->trans('notification.comment_created');
    }

    public function getDescription(Notification $notification): string
    {
        return '';
    }

    public function getImage(Notification $notification): string
    {
        return '';
    }

    public function getUrl(Notification $notification): string
    {
        return '';
    }

    public function getEmailTemplate(Notification $notification): string
    {
        return '@Forumify/emails/notifications/comment_created.html.twig';
    }
}
