<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Entity\Notification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractEmailNotificationType implements NotificationTypeInterface
{
    protected readonly MailerInterface $mailer;

    #[Required]
    public function setMailer(MailerInterface $mailer): void
    {
        $this->mailer = $mailer;
    }

    protected function shouldSendEmail(Notification $notification): bool
    {
        return $notification
            ->getRecipient()
            ->getNotificationSettings()
            ->isEmailOnNotification();
    }

    abstract public function getEmailTemplate(Notification $notification): string;

    public function handleNotification(Notification $notification): void
    {
        if (!$this->shouldSendEmail($notification)) {
            return;
        }

        $recipient = $notification->getRecipient();
        $email = (new TemplatedEmail())
            ->from('noreply@forumify.net')
            ->to(new Address($recipient->getEmail(), $recipient->getUsername()))
            ->subject($this->getTitle($notification))
            ->htmlTemplate($this->getEmailTemplate($notification))
            ->context($notification->getDeserializedContext());

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $ex) {
            throw new NotificationHandlerException('Unable to send email', $ex->getCode(), $ex);
        }
    }
}
