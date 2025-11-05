<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\UserManageBadgesType;
use Forumify\Admin\Form\UserManageRolesType;
use Forumify\Admin\Form\UserType;
use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<User>
 */
#[Route('/users', 'users')]
class UserController extends AbstractCrudController
{
    protected bool $allowCreate = false;

    protected ?string $permissionView = 'forumify.admin.users.view';
    protected ?string $permissionCreate = 'forumify.admin.users.manage';
    protected ?string $permissionEdit = 'forumify.admin.users.manage';
    protected ?string $permissionDelete = 'forumify.admin.users.manage';

    protected function getEntityClass(): string
    {
        return User::class;
    }

    protected function getTableName(): string
    {
        return 'UserTable';
    }

    /**
     * @param User|null $data
     * @return FormInterface<object|null>
     */
    protected function getForm(?object $data): FormInterface
    {
        /** @var FormInterface<object|null> */
        return $this->createForm(UserType::class, $data);
    }

    #[Route('/{identifier}/delete', '_delete')]
    public function delete(Request $request, string $identifier): Response
    {
        $user = $this->repository->find($identifier);
        if ($user !== null) {
            $this->denyAccessUnlessGranted(VoterAttribute::UserDelete->value, $user);
        }

        return parent::delete($request, $identifier);
    }

    #[Route('/{id}/badges', '_badges')]
    #[IsGranted('forumify.admin.users.manage_badges')]
    public function manageBadges(User $user, Request $request): Response
    {
        $form = $this->createForm(UserManageBadgesType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @phpstan-ignore-next-line */
            $this->save(false, $form);

            $this->addFlash('success', 'admin.user.manage_badges.success');
            return $this->redirectToRoute('forumify_admin_users_list');
        }

        return $this->render('@Forumify/form/simple_form_page.html.twig', [
            'admin' => true,
            'title' => 'admin.user.manage_badges.title',
            'titleArgs' => ['username' => $user->getDisplayName()],
            'form' => $form->createView(),
            'cancelPath' => $this->generateUrl('forumify_admin_users_list'),
        ]);
    }

    #[Route('/{id}/roles', '_roles')]
    #[IsGranted('forumify.admin.users.manage_roles')]
    public function manageRoles(User $user, Request $request): Response
    {
        $form = $this->createForm(UserManageRolesType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @phpstan-ignore-next-line */
            $this->save(false, $form);

            $this->addFlash('success', 'admin.user.manage_roles.success');
            return $this->redirectToRoute('forumify_admin_users_list');
        }

        return $this->render('@Forumify/form/simple_form_page.html.twig', [
            'admin' => true,
            'title' => 'admin.user.manage_roles.title',
            'titleArgs' => ['username' => $user->getDisplayName()],
            'form' => $form->createView(),
            'cancelPath' => $this->generateUrl('forumify_admin_users_list'),
        ]);
    }
}
