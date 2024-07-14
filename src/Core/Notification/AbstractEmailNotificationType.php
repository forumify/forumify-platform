<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Repository\SettingRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractEmailNotificationType implements NotificationTypeInterface
{
    protected readonly MailerInterface $mailer;
    private readonly SettingRepository $settingRepository;

    #[Required]
    public function setServices(MailerInterface $mailer, SettingRepository $settingRepository): void
    {
        $this->mailer = $mailer;
        $this->settingRepository = $settingRepository;
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
        $isDemo = (bool)($_SERVER['FORUMIFY_DEMO'] ?? false);
        if ($isDemo || !$this->shouldSendEmail($notification)) {
            return;
        }

        $isCloudInstance = (bool)($_SERVER['FORUMIFY_HOSTED_INSTANCE'] ?? false);
        $from = $isCloudInstance
            ? 'noreply@forumify.net'
            : $this->settingRepository->get('forumify.mailer.from');

        if ($from === null) {
            throw new NotificationHandlerException('forumify.mailer.from is not configured.');
        }

        $forumName = $this->settingRepository->get('forumify.title') ?? 'forumify';

        $recipient = $notification->getRecipient();
        $email = (new TemplatedEmail())
            ->from($from)
            ->to(new Address($recipient->getEmail(), $recipient->getUsername()))
            ->subject($forumName . ' | ' . $this->getTitle($notification))
            ->htmlTemplate($this->getEmailTemplate($notification))
            ->context([
                'notification' => $notification,
                'this' => $this,
                ...$notification->getDeserializedContext(),
            ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $ex) {
            throw new NotificationHandlerException('Unable to send email', $ex->getCode(), $ex);
        }
    }
}
