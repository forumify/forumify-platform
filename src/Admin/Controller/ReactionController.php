<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\ReactionType;
use Forumify\Forum\Entity\Reaction;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reactions', 'reactions')]
class ReactionController extends AbstractCrudController
{
    protected ?string $permissionView = 'forumify.admin.settings.reactions.view';
    protected ?string $permissionCreate = 'forumify.admin.settings.reactions.manage';
    protected ?string $permissionEdit = 'forumify.admin.settings.reactions.manage';
    protected ?string $permissionDelete = 'forumify.admin.settings.reactions.manage';

    protected function getEntityClass(): string
    {
        return Reaction::class;
    }

    protected function getTableName(): string
    {
        return 'ReactionTable';
    }

    protected function getForm(?object $data): FormInterface
    {
        return $this->createForm(ReactionType::class, $data, [
            'image_required' => $data === null,
        ]);
    }
}
