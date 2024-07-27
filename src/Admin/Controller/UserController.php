<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\UserType;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Service\MediaService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users', 'user')]
#[IsGranted('forumify.admin.users.view')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MediaService $mediaService,
        private readonly FilesystemOperator $avatarStorage,
    ) {
    }

    #[Route('', '_list')]
    public function list(): Response
    {
        return $this->render('@Forumify/admin/user/user_list.html.twig');
    }

    #[Route('/{username}', '')]
    #[IsGranted('forumify.admin.users.manage')]
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $newAvatar = $form->get('newAvatar')->getData();
            if ($newAvatar !== null) {
                $avatar = $this->mediaService->saveToFilesystem($this->avatarStorage, $newAvatar);
                $user->setAvatar($avatar);
            }

            $this->userRepository->save($user);

            $this->addFlash('success', 'flashes.user_saved');
            return $this->redirectToRoute('forumify_admin_user_list');
        }

        return $this->render('@Forumify/admin/user/user.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{username}/delete', '_delete')]
    #[IsGranted('forumify.admin.users.manage')]
    public function delete(User $user, Request $request): Response
    {
        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/admin/user/delete.html.twig', [
                'user' => $user,
            ]);
        }

        $this->userRepository->remove($user);
        $this->addFlash('success', 'flashes.user_removed');
        return $this->redirectToRoute('forumify_admin_user_list');
    }
}
