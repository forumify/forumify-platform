<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\BadgeType;
use Forumify\Forum\Entity\Badge;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractCrudController<Badge>
 */
#[Route('/badges', 'badges')]
class BadgeController extends AbstractCrudController
{
    protected ?string $permissionView = 'forumify.admin.settings.badges.view';
    protected ?string $permissionCreate = 'forumify.admin.settings.badges.manage';
    protected ?string $permissionEdit = 'forumify.admin.settings.badges.manage';
    protected ?string $permissionDelete = 'forumify.admin.settings.badges.manage';

    protected function getEntityClass(): string
    {
        return Badge::class;
    }

    protected function getTableName(): string
    {
        return 'BadgeTable';
    }

    /**
     * @param Badge|null $data
     * @return FormInterface<object|null>
     */
    protected function getForm(?object $data): FormInterface
    {
        /** @var FormInterface<object|null> */
        return $this->createForm(BadgeType::class, $data, [
            'image_required' => $data === null,
        ]);
    }
}
