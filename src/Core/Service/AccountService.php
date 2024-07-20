<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use DateInterval;
use DateTime;
use Exception;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountService
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Mailer $mailer,
        private readonly LoggerInterface $logger,
        private readonly TokenService $tokenService,
        private readonly Security $security,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function sendVerificationEmail(User $user): void
    {
        $subject = $this->translator->trans('email_verification.email_subject');
        $email = (new TemplatedEmail())
            ->subject($subject)
            ->htmlTemplate("@Forumify/emails/verify_mail.html.twig")
            ->context([
                'user' => $user,
                'token' => $this->tokenService->createJwt(
                    $user,
                    (new DateTime())->add(new DateInterval('P1D')),
                    ['verify-email']
                ),
            ]);

        try {
            $this->mailer->send($email, $user);
        } catch (Exception|TransportExceptionInterface $ex) {
            $this->logger->error($ex->getMessage(), ['context' => $ex]);
        }
    }

    public function verifyEmailVerificationToken(string $token): void
    {
        $payload = $this->tokenService->decodeToken($token);
        if (!in_array('verify-email', $payload['resource_access'] ?? [], true)) {
            throw new AccessDeniedException('This token can not be used to verify your email.');
        }

        $user = $this->security->getUser();
        if ($payload['sub'] !== $user?->getUserIdentifier()) {
            throw new AccessDeniedException('This token does not belong to you.');
        }
    }

    public function sendPasswordForgetEmail(User $user): void
    {
        $subject = $this->translator->trans('forgot_password.email_subject');
        $email = (new TemplatedEmail())
            ->subject($subject)
            ->htmlTemplate("@Forumify/emails/password_reset.html.twig")
            ->context([
                'user' => $user,
                'token' => $this->tokenService->createJwt(
                    $user,
                    (new DateTime())->add(new DateInterval('PT15M')),
                    ['password-reset']
                ),
            ]);

        try {
            $this->mailer->send($email, $user);
        } catch (Exception|TransportExceptionInterface $ex) {
            $this->logger->error($ex->getMessage(), ['context' => $ex]);
        }
    }

    public function getUserForPasswordReset(string $token): User
    {
        $payload = $this->tokenService->decodeToken($token);
        if (!in_array('password-reset', $payload['resource_access']?? [], true)) {
            throw new AccessDeniedException('This token can not be used for password reset.');
        }

        $user = $this->userRepository->findOneBy(['username' => $payload['sub']]);
        if ($user === null) {
            throw new AccessDeniedException('No user found for this token.');
        }

        return $user;
    }
}
