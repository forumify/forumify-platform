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

class UserController extends AbstractController
{
    #[Route('/user/{username}', 'user')]
    public function __invoke(User $user, Request $request, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $userRepository->save($user);

            $this->addFlash('success', 'flashes.user_saved');
            return $this->redirectToRoute('forumify_admin_user_list');
        }

        return $this->render('@Forumify/admin/user/user.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
