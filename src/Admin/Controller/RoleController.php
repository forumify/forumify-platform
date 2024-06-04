<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\RoleType;
use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\PermissionRepository;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/roles', 'role')]
class RoleController extends AbstractController
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        PermissionRepository $permissionRepository,
        PluginRepository $pluginRepository,
    )
    {
        $this->permissionRepository = $permissionRepository;
        $this->pluginRepository = $pluginRepository;
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
        $permissions = $this->permissionRepository->findAll();
        $form = $this->createForm(RoleType::class, $role, ['permissions' => $permissions]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->getData();

            foreach ($permissions as $permission) {
                $permissionId = 'permission_' . $permission->getId();
                $hasPermission = $form->get($permissionId)->getData();

                if ($hasPermission && !$role->getPermissions()->contains($permission)) {
                    $role->addPermission($permission);
                } elseif (!$hasPermission && $role->getPermissions()->contains($permission)) {
                    $role->removePermission($permission);
                }
            }

            $this->roleRepository->save($role);

            $this->addFlash('success', 'flashes.role_saved');
            return $this->redirectToRoute('forumify_admin_role_list');
        }

        $plugins = $this->pluginRepository->findAll();
        $permissionsByPlugin = [];

        foreach ($plugins as $plugin) {
            foreach ($plugin->getPermissions() as $permission) {
                $permissionParts = explode('.', $permission->getPermission());

                if (count($permissionParts) >= 4) {
                    [$_, $category, $subcategory, $permissionName] = $permissionParts;
                    $permissionsByPlugin[$plugin->getId()][$category][$subcategory][] = [
                        'name' => $permissionName,
                        'id' => $permission->getId(),
                    ];
                }
            }
        }

        return $this->render('@Forumify/admin/role/role.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
            'plugins' => $plugins,
            'permissionsByPlugin' => $permissionsByPlugin,


        ]);
    }
}
