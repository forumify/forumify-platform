<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Entity\Notification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

abstract class AbstractEmailNotificationType implements NotificationTypeInterface
{
    public function __construct(protected readonly MailerInterface $mailer)
    {
    }

    protected function shouldSendEmail(Notification $notification): bool
    {
        return $notification
            ->getRecipient()
            ->getNotificationSettings()
            ->isEmailOnNotification();
    }

    public function handleNotification(Notification $notification): void
    {
        if (!$this->shouldSendEmail($notification)) {
            return;
        }

        $recipient = $notification->getRecipient();
        $email = (new TemplatedEmail())
            ->from('noreply@forumify.net')
            ->to(new Address($recipient->getEmail(), $recipient->getUsername()))
            ->subject($this->getSubject())
            ->htmlTemplate($this->getTemplate())
            ->context($notification->getContext());

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $ex) {
            throw new NotificationHandlerException('Unable to send email', $ex->getCode(), $ex);
        }
    }
}
