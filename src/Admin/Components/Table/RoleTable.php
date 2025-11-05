<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\SortableEntityInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('RoleTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.roles.view')]
class RoleTable extends AbstractDoctrineTable
{
    protected ?string $permissionReorder = 'forumify.admin.settings.roles.manage';

    protected function getEntityClass(): string
    {
        return Role::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addPositionColumn()
            ->addColumn('title', [
                'field' => 'title',
                'renderer' => [$this, 'renderTitleColumn'],
            ])
            ->addColumn('actions', [
                'label' => '',
                'field' => 'id',
                'searchable' => false,
                'sortable' => false,
                'renderer' => [$this, 'renderActionColumn'],
            ]);
    }

    /**
     * @param Role $entity
     */
    protected function canReorder(object $entity): bool
    {
        return !$entity->isSystem() && parent::canReorder($entity);
    }

    protected function reorderItem(object $entity, string $direction): void
    {
        /** @var Role&SortableEntityInterface $entity */
        $this->repository->reorder($entity, $direction, fn (QueryBuilder $qb) => $qb->andWhere('e.system = 0'));
    }

    protected function renderTitleColumn(string $title, Role $role): string
    {
        return "
            <p>$title</p>
            <p class='text-small'>{$role->getDescription()}</p>
        ";
    }

    protected function renderActionColumn(int $id, Role $role): string
    {
        if ($role->isSystem() || !$this->security->isGranted('forumify.admin.settings.roles.manage')) {
            return '';
        }

        $actions = '';
        $actions .= $this->renderAction('forumify_admin_roles_edit', ['identifier' => $id], 'pencil-simple-line');
        $actions .= $this->renderAction('forumify_admin_roles_delete', ['identifier' => $id], 'x');
        return $actions;
    }
}
