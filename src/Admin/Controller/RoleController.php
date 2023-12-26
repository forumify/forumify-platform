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

#[Route('/roles', 'role')]
class RoleController extends AbstractController
{
    public function __construct(private readonly RoleRepository $roleRepository)
    {
    }

    #[Route('', '_list')]
    public function list(): Response
    {
        return $this->render('@Forumify/admin/role/role_list.html.twig');
    }

    #[Route('/create', '_create', priority: 1)]
    public function create(Request $request): Response
    {
        return $this->handleForm($request);
    }

    #[Route('/{id<\d+>}', '')]
    public function edit(Role $role, Request $request): Response
    {
        return $this->handleForm($request, $role);
    }

    #[Route('/{id<\d+>}/delete', '_delete')]
    public function delete(Role $role): Response
    {
        $this->roleRepository->remove($role);

        $this->addFlash('success', 'flashes.role_removed');
        return $this->redirectToRoute('forumify_admin_role_list');
    }

    private function handleForm(Request $request, ?Role $role = null): Response
    {
        $form = $this->createForm(RoleType::class, $role);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->getData();
            $this->roleRepository->save($role);

            $this->addFlash('success', 'flashes.role_saved');
            return $this->redirectToRoute('forumify_admin_role_list');
        }

        return $this->render('@Forumify/admin/role/role.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
        ]);
    }
}
