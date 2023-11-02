<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Core\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoleListController extends AbstractController
{
    #[Route('/roles', name: 'role_list')]
    public function __invoke(RoleRepository $roleRepository): Response
    {
        $roles = $roleRepository->findAll();

        return $this->render('@Forumify/admin/role/role_list.html.twig', [
            'roles' => $roles,
        ]);
    }
}
