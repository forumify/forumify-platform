<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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
    ) {
    }

    #[Route('/forgot-password', name: 'forgot_password')]
    public function __invoke(Request $request): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('forumify_core_settings');
        }

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'email',
                'help' => 'forgot_password.email_help',
                'attr' => ['autocomplete' => 'email', 'autofocus' => 'true'],
            ])
            ->getForm();

        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@Forumify/frontend/auth/forgot_password/search.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $email = $form->getData()['email'];
        $user = $this->userRepository->findOneBy(['email' => $email]);
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
