<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Service\AccountService;
use Forumify\Core\Service\RecaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

class ForgotPasswordController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AccountService $accountService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SettingRepository $settingRepository,
        private readonly RecaptchaService $recaptchaService,
    ) {
    }

    #[Route('/forgot-password', name: 'forgot_password')]
    public function __invoke(Request $request): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('forumify_core_settings');
        }

        $form = $this->createFormBuilder()
            ->add('query', TextType::class, [
                'label' => 'Username or email',
                'help' => 'forgot_password.email_help',
                'attr' => ['autofocus' => 'true'],
            ])
            ->getForm();

        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@Forumify/frontend/auth/forgot_password/search.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $trapChecked = $request->get('human');
        if ($trapChecked) {
            return $this->redirectToRoute('forumify_core_index');
        }

        if ($this->settingRepository->get('forumify.recaptcha.enabled')) {
            $score = $this->recaptchaService->verifyRequest($request);
            if ($score < 0.8) {
                // most likely a bot
                $this->addFlash('error', 'flashes.bot_detected');
                return $this->redirectToRoute('forumify_core_index');
            }
        }

        $query = $form->get('query')->getData();
        $user = $this->userRepository
            ->createQueryBuilder('u')
            ->where('u.email = :q')
            ->orWhere('u.username = :q')
            ->setParameter('q', $query)
            ->getQuery()
            ->getSingleResult()
        ;

        if ($user !== null) {
            $this->accountService->sendPasswordForgetEmail($user);
        }
        return $this->render('@Forumify/frontend/auth/forgot_password/email_sent.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'reset_password', requirements: ['token' => '.+'])]
    public function resetPassword(string $token, Request $request): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('forumify_core_settings');
        }

        $form = $this->createFormBuilder()
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password', 'attr' => ['autocomplete' => 'new-password', 'autofocus' => 'autofocus']],
                'second_options' => ['label' => 'Repeat password', 'attr' => ['autocomplete' => 'new-password']],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 8)
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@Forumify/frontend/auth/forgot_password/reset_password.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $user = $this->accountService->getUserForPasswordReset($token);
        $user->setPassword($this->passwordHasher->hashPassword($user, $form->getData()['newPassword']));
        $this->userRepository->save($user);

        $this->addFlash('success', 'forgot_password.password_changed');
        return $this->redirectToRoute('forumify_core_login');
    }
}
