<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoleDeleteController extends AbstractController
{
    #[Route('/roles/{id}/delete', 'role_delete')]
    public function __invoke(Role $role, RoleRepository $roleRepository): Response
    {
        $roleRepository->remove($role);

        $this->addFlash('success', 'flashes.role_removed');
        return $this->redirectToRoute('forumify_admin_role_list');
    }
}
