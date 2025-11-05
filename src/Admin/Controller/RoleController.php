<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\RoleType;
use Forumify\Core\Entity\Role;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractCrudController<Role>
 */
#[Route('/roles', 'roles')]
class RoleController extends AbstractCrudController
{
    protected ?string $permissionView = 'forumify.admin.settings.roles.view';
    protected ?string $permissionCreate = 'forumify.admin.settings.roles.manage';
    protected ?string $permissionEdit = 'forumify.admin.settings.roles.manage';
    protected ?string $permissionDelete = 'forumify.admin.settings.roles.manage';

    protected string $formTemplate = '@Forumify/admin/role/role.html.twig';

    protected function getEntityClass(): string
    {
        return Role::class;
    }

    protected function getTableName(): string
    {
        return 'RoleTable';
    }

    /**
     * @param Role|null $data
     * @return FormInterface<object|null>
     */
    protected function getForm(?object $data): FormInterface
    {
        /** @var FormInterface<object|null> */
        return $this->createForm(RoleType::class, $data);
    }
}
