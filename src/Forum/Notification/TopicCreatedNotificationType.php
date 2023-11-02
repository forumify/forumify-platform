<?php

declare(strict_types=1);

namespace Forumify\Forum\Notification;

use Forumify\Core\Notification\AbstractEmailNotificationType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TopicCreatedNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'topic_created';

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
        return '@Forumify/emails/notifications/topic_created.html.twig';
    }

    public function getSubject(): string
    {
        return $this->translator->trans('notification.topic_created');
    }
}
