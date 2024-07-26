<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Exception;
use Firebase\JWT\ExpiredException;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class EmailVerificationController extends AbstractController
{
    #[Route('/verify-email/{token?}', name: 'verify_email', requirements: ['token' => '.+'])]
    public function verifyEmail(
        ?string $token,
        AccountService $accountService,
        UserRepository $userRepository,
    ): Response {
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
            $accountService->verifyEmailVerificationToken($token);
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
    public function resendEmailVerification(AccountService $accountService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isEmailVerified()) {
            $this->addFlash('info', 'flashes.email_already_verified');
            return $this->redirectToRoute('forumify_core_index');
        }

        $accountService->sendVerificationEmail($user);
        return $this->redirectToRoute('forumify_core_verify_email');
    }
}
