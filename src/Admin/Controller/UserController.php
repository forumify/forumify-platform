<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\UserType;
use Forumify\Core\Entity\User;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users', 'users')]
class UserController extends AbstractCrudController
{
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
     */
    protected function getForm(?object $data): FormInterface
    {
        $oldRoles = $data?->getRoles() ?? [];
        return $this->createForm(UserType::class, $data, [
            'is_super_admin' => in_array('ROLE_SUPER_ADMIN', $oldRoles, true)
        ]);
    }
}
