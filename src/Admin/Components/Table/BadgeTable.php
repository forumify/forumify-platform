<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Forum\Entity\Badge;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('BadgeTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.badges.view')]
class BadgeTable extends AbstractDoctrineTable
{
    protected ?string $permissionReorder = 'forumify.admin.settings.badges.manage';

    protected function getEntityClass(): string
    {
        return Badge::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addPositionColumn()
            ->addColumn('name', [
                'field' => 'name',
            ])
            ->addColumn('actions', [
                'field' => 'id',
                'label' => '',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderActionColumn(...),
            ]);
    }

    protected function renderActionColumn(int $id): string
    {
        if (!$this->security->isGranted('forumify.admin.settings.badges.manage')) {
            return '';
        }

        $actions = '';
        $actions .= $this->renderAction('forumify_admin_badges_edit', ['identifier' => $id], 'pencil-simple-line');
        $actions .= $this->renderAction('forumify_admin_badges_delete', ['identifier' => $id], 'x');
        return $actions;
    }
}
