<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Form\RegisterType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\CreateUserService;
use Forumify\Core\Service\RecaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('forumify_core_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@Forumify/frontend/auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): never
    {
        throw new \LogicException('This should not be reached');
    }

    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        CreateUserService $createUserService,
        Security $security,
        SettingRepository $settingRepository,
        RecaptchaService $recaptchaService,
    ): Response {
        if (!$settingRepository->get('forumify.enable_registrations')) {
            $this->addFlash('error', 'flashes.registration_disabled');
            return $this->redirectToRoute('forumify_core_index');
        }

        if ($this->getUser() !== null) {
            return $this->redirectToRoute('forumify_core_index');
        }

        $form = $this->createForm(RegisterType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($settingRepository->get('forumify.recaptcha.enabled')) {
                $score = $recaptchaService->verifyRequest($request);
                if ($score < 0.8) {
                    // most likely a bot
                    $this->addFlash('error', 'flashes.bot_detected');
                    return $this->redirectToRoute('forumify_core_register');
                }
            }

            $user = $createUserService->createUser($form->getData());
            $security->login($user, 'security.authenticator.form_login.main');
            return $this->redirectToRoute('forumify_core_verify_email');
        }

        return $this->render('@Forumify/frontend/auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
