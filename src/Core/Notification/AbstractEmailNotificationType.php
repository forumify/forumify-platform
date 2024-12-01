<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Exception;
use Forumify\Core\Entity\Notification;
use Forumify\Core\Service\Mailer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractEmailNotificationType implements NotificationTypeInterface
{
    protected Mailer $mailer;

    #[Required]
    public function setServices(Mailer $mailer): void
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

    /** @inheritDoc */
    public function handleNotification(Notification $notification): void
    {
        if (!$this->shouldSendEmail($notification)) {
            return;
        }

        $email = (new TemplatedEmail())
            ->subject($this->getTitle($notification))
            ->htmlTemplate($this->getEmailTemplate($notification))
            ->context([
                'notification' => $notification,
                'this' => $this,
                ...($notification->getDeserializedContext() ?? []),
            ]);

        try {
            $this->mailer->send($email, $notification->getRecipient());
        } catch (TransportExceptionInterface|Exception $ex) {
            throw new NotificationHandlerException('Unable to send email', $ex->getCode(), $ex);
        }
    }
}
