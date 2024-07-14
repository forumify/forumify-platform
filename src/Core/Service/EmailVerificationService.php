<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use DateInterval;
use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailVerificationService
{
    public function __construct(
        private readonly string $appSecret,
        private readonly UserRepository $userRepository,
        private readonly SettingRepository $settingRepository,
        private readonly TranslatorInterface $translator,
        private readonly Mailer $mailer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function sendEmailVerificationEmail(User $user): void
    {
        $verificationToken = $this->createToken($user);

        $subject = $this->translator->trans('email_subjects.account_verification', [
            'forum_name' => $this->settingRepository->get('forumify.title')
        ]);

        $email = (new TemplatedEmail())
            ->subject($subject)
            ->htmlTemplate("@Forumify/emails/verify_mail.html.twig")
            ->context([
                'user' => $user,
                'token' => $verificationToken,
            ]);

        try {
            $this->mailer->send($email, $user);
        } catch (TransportExceptionInterface $ex) {
            $this->logger->error($ex->getMessage(), ['context' => $ex]);
        }
    }

    public function createToken(User $user): string
    {
        $now = new DateTime();

        $payload = [
            'exp' => $now->add(new DateInterval('P1D'))->getTimestamp(),
            'sub' => $user->getId(),
        ];

        $jwt = JWT::encode($payload, $this->appSecret, 'HS256');
        return base64_encode($jwt);
    }

    public function verifyTokenForUser(string $token, User $user): void
    {
        $jwt = base64_decode($token);
        $payload = JWT::decode($jwt, new Key($this->appSecret, 'HS256'));
        $userFromPayload = $this->userRepository->find($payload->sub ?? 0);

        if ($userFromPayload?->getUserIdentifier() !== $user->getUserIdentifier()) {
            throw new AccessDeniedException('Invalid token.');
        }
    }
}
