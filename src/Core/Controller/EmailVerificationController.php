<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Exception;
use Firebase\JWT\ExpiredException;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Service\EmailVerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmailVerificationController extends AbstractController
{
    #[Route('/verify-email/{token?}', name: 'verify_email')]
    public function verifyEmail(
        ?string $token,
        EmailVerificationService $verificationService,
        UserRepository $userRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        if ($user->isEmailVerified()) {
            $this->addFlash('info', 'flashes.email_already_verified');
            return $this->redirectToRoute('forumify_core_index');
        }

        if ($token === null) {
            return $this->render('@Forumify/frontend/auth/verify_email.html.twig', [
                'user' => $user
            ]);
        }

        $error = null;
        try {
            $verificationService->verifyTokenForUser($token, $user);
        } catch (ExpiredException) {
            $error = 'expired';
        } catch (Exception) {
            $error = 'invalid_token';
        }

        if ($error !== null) {
            $this->addFlash('error', 'flashes.email_verification_invalid');
            return $this->render('@Forumify/frontend/auth/verify_email.html.twig', [
                'user' => $user,
                'error' => $error,
            ]);
        }

        $user->setEmailVerified(true);
        $userRepository->save($user);

        $this->addFlash('success', 'flashes.email_verification_successful');
        return $this->redirectToRoute('forumify_core_index');
    }

    #[Route('/verify-email/resend', name: 'verify_email_resend', priority: 1)]
    public function resendEmailVerification(EmailVerificationService $verificationService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        if ($user->isEmailVerified()) {
            $this->addFlash('info', 'flashes.email_already_verified');
            return $this->redirectToRoute('forumify_core_index');
        }

        $verificationService->sendEmailVerificationEmail($user);
        return $this->redirectToRoute('forumify_core_verify_email');
    }
}
