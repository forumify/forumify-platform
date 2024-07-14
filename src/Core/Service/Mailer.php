<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\SettingRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Mailer
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly MailerInterface $mailer
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(Email $email, ?User $recipient = null): void
    {
        $isDemo = (bool)($_SERVER['FORUMIFY_DEMO'] ?? false);
        if ($isDemo) {
            return;
        }

        $isCloudInstance = (bool)($_SERVER['FORUMIFY_HOSTED_INSTANCE'] ?? false);
        $fromAddress = $isCloudInstance ? 'noreply@forumify.net' : $this->settingRepository->get('forumify.mailer.from');
        $fromName = $this->settingRepository->get('forumify.title') ?? 'forumify';
        $from = new Address($fromAddress, $fromName);
        $email->from($from);

        if ($recipient !== null) {
            $to = new Address($recipient->getEmail(), $recipient->getDisplayName());
            $email->to($to);
        }

        $this->mailer->send($email);
    }
}
