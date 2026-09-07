<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\UserManageBadgesType;
use Forumify\Admin\Form\UserManageRolesType;
use Forumify\Admin\Form\UserType;
use Forumify\Core\Entity\User;
use Forumify\Core\Event\UserBannedEvent;
use Forumify\Core\Event\UserDeletedEvent;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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

    protected function getForm(?object $data): FormInterface
    {
        return $this->createForm(UserType::class, $data);
    }

    #[Route('/{identifier}/delete', '_delete')]
    #[IsGranted('forumify.admin.users.manage')]
    public function delete(Request $request, string $identifier): Response
    {
        $user = $this->repository->find($identifier);
        if ($user === null) {
            return $this->redirectToRoute('forumify_admin_users_list');
        }

        $this->denyAccessUnlessGranted(VoterAttribute::UserDelete->value, $user);

        if ($request->query->getBoolean('confirmed')) {
            return $this->deleteUser($user, $request->query->getBoolean('deleteContent'));
        }

        $form = $this->createDeleteContentForm('admin.user.delete.');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('deleteContent')->getData() !== true) {
                return $this->deleteUser($user, false);
            }

            return $this->confirmDeleteContent($user, 'admin.user.delete.', 'forumify_admin_users_delete', [
                'identifier' => $user->getId(),
            ]);
        }

        return $this->render('@Forumify/admin/user/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    private function deleteUser(User $user, bool $deleteContent): Response
    {
        $this->eventDispatcher->dispatch(new UserDeletedEvent($user, $deleteContent));
        $this->repository->remove($user);

        $this->addFlash('success', $deleteContent
            ? 'admin.user.delete.deleted_and_content'
            : 'admin.user.delete.deleted');

        return $this->redirectToRoute('forumify_admin_users_list');
    }

    #[Route('/{id}/ban', '_ban')]
    #[IsGranted('forumify.admin.users.manage')]
    public function ban(User $user, Request $request): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::UserBan->value, $user);

        if ($user->isBanned()) {
            return $this->redirectToRoute('forumify_admin_users_list');
        }

        if ($request->query->getBoolean('confirmed')) {
            return $this->banUser($user, $request->query->getBoolean('deleteContent'));
        }

        $form = $this->createDeleteContentForm('admin.user.ban.');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('deleteContent')->getData() !== true) {
                return $this->banUser($user, false);
            }

            return $this->confirmDeleteContent($user, 'admin.user.ban.', 'forumify_admin_users_ban', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('@Forumify/admin/user/ban.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    private function banUser(User $user, bool $deleteContent): Response
    {
        $user->setBanned(true);
        $user->setRoleEntities([]);
        $this->repository->save($user);

        $this->eventDispatcher->dispatch(new UserBannedEvent($user, $deleteContent));

        $this->addFlash('success', $deleteContent
            ? 'admin.user.ban.banned_and_deleted'
            : 'admin.user.ban.banned');

        return $this->redirectToRoute('forumify_admin_users_list');
    }

    /**
     * @return FormInterface<null>
     */
    private function createDeleteContentForm(string $translationPrefix): FormInterface
    {
        return $this->createFormBuilder()
            ->add('deleteContent', CheckboxType::class, [
                'label' => $translationPrefix . 'delete_content',
                'help' => $translationPrefix . 'delete_content_help',
                'required' => false,
            ])
            ->getForm();
    }

    /**
     * @param array<string, mixed> $routeParameters
     */
    private function confirmDeleteContent(
        User $user,
        string $translationPrefix,
        string $route,
        array $routeParameters,
    ): Response {
        return $this->render('@Forumify/admin/user/delete_content_confirm.html.twig', [
            'user' => $user,
            'translationPrefix' => $translationPrefix,
            'confirmPath' => $this->generateUrl($route, $routeParameters + ['deleteContent' => 1, 'confirmed' => 1]),
            'cancelPath' => $this->generateUrl($route, $routeParameters),
        ]);
    }

    #[Route('/{id}/badges', '_badges')]
    #[IsGranted('forumify.admin.users.manage_badges')]
    public function manageBadges(User $user, Request $request): Response
    {
        /** @var FormInterface<User|null> $form */
        $form = $this->createForm(UserManageBadgesType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
        /** @var FormInterface<User|null> $form */
        $form = $this->createForm(UserManageRolesType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
