<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\UserType;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users', 'user')]
class UserController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    #[Route('', '_list')]
    public function list(): Response
    {
        return $this->render('@Forumify/admin/user/user_list.html.twig');
    }

    #[Route('/{username}', '')]
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
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
    public function delete(User $user, Request $request): Response
    {
        if (!$request->get('confirmed')) {
            return  $this->render('@Forumify/admin/user/delete.html.twig', [
                'user' => $user,
            ]);
        }

        $this->userRepository->remove($user);
        $this->addFlash('success', 'flashes.user_removed');
        return $this->redirectToRoute('forumify_admin_user_list');
    }
}
