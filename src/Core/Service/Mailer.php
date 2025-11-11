<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Exception;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\SettingRepository;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Mailer
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly MailerInterface $mailer,
        #[Autowire(env: 'bool:FORUMIFY_DEMO')]
        private readonly bool $isDemo,
        #[Autowire(env: 'bool:FORUMIFY_HOSTED_INSTANCE')]
        private readonly bool $isHostedInstance,
    ) {
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function send(Email $email, ?User $recipient = null): void
    {
        if ($this->isDemo) {
            return;
        }

        $fromAddress = $this->isHostedInstance
            ? 'noreply@forumify.net'
            : $this->settingRepository->get('forumify.mailer.from')
        ;
        if (empty($fromAddress)) {
            throw new RuntimeException('"forumify.mailer.from" setting is not configured.');
        }

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
