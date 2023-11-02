<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Core\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserListController extends AbstractController
{
    #[Route('/users', 'user_list')]
    public function __invoke(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('@Forumify/admin/user/user_list.html.twig', [
            'users' => $users
        ]);
    }
}
