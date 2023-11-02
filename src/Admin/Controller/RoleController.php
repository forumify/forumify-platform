<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\RoleType;
use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoleController extends AbstractController
{
    #[Route('/role/{id}', 'role')]
    public function __invoke(Role $role, Request $request, RoleRepository $roleRepository): Response
    {
        $form = $this->createForm(RoleType::class, $role);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->getData();
            $roleRepository->save($role);

            $this->addFlash('success', 'flashes.role_saved');
            return $this->redirectToRoute('forumify_admin_role_list');
        }

        return $this->render('@Forumify/admin/role/role.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }
}
