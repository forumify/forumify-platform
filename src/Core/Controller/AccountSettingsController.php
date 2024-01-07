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
use Symfony\Component\Routing\Attribute\Route;

class AccountSettingsController extends AbstractController
{
    #[Route('/settings', name: 'settings')]
    public function __invoke(
        Request $request,
        UserRepository $userRepository,
        MediaService $mediaService,
        FilesystemOperator $avatarStorage,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('forumify_core_index');
        }

        $form = $this->createForm(AccountSettingsType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $newAvatar = $form->get('newAvatar')->getData();
            if ($newAvatar !== null) {
                $avatar = $mediaService->saveToFilesystem($avatarStorage, $newAvatar);
                $user->setAvatar($avatar);
            }

            $userRepository->save($user);
            $this->addFlash('success', 'flashes.account_settings_saved');
        }

        return $this->render('@Forumify/frontend/settings/settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
