<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Service\MediaService;
use Forumify\Forum\Form\AccountSettingsType;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AccountSettingsController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MediaService $mediaService,
        private readonly FilesystemOperator $avatarStorage,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/settings', name: 'settings')]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(AccountSettingsType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $newAvatar = $form->get('newAvatar')->getData();
            if ($newAvatar !== null) {
                $avatar = $this->mediaService->saveToFilesystem($this->avatarStorage, $newAvatar);
                $user->setAvatar($avatar);
            }

            $newPassword = $form->get('newPassword')->getData();
            if ($newPassword) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
            }

            $this->userRepository->save($user);
            $this->addFlash('success', 'flashes.account_settings_saved');
            return $this->redirectToRoute('forumify_core_settings');
        }

        return $this->render('@Forumify/frontend/settings/settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
