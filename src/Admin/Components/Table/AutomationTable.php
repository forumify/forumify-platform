<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Automation\Entity\Automation;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\AutomationTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.automations.view')]
class AutomationTable extends AbstractDoctrineTable
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function getEntityClass(): string
    {
        return Automation::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('name', [
                'field' => 'name',
            ])
            ->addColumn('trigger', [
                'field' => 'trigger',
            ])
            ->addColumn('action', [
                'field' => 'action',
            ])
            ->addColumn('enabled', [
                'field' => 'enabled',
                'searchable' => false,
                'renderer' => fn (bool $enabled) => '<input type="checkbox" disabled="disabled" ' . ($enabled ? 'checked' : '') . ' />',
            ])
            ->addColumn('actions', [
                'field' => 'id',
                'label' => '',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderActions(...),
            ])
        ;
    }

    private function renderActions(int $id): string
    {
        if (!$this->security->isGranted('forumify.admin.settings.automations.manage')) {
            return '';
        }

        $actions = $this->renderAction('forumify_admin_automation_form', ['id' => $id], 'pencil-simple-line');
        $actions .= $this->renderAction('forumify_admin_automation_delete', ['id' => $id], 'x');

        return $actions;
    }
}
