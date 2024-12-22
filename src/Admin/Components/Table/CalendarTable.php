<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Calendar\Entity\Calendar;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\CalendarTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.calendars.view')]
class CalendarTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function getEntityClass(): string
    {
        return Calendar::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('title', [
                'field' => 'title',
            ])
            ->addColumn('color', [
                'field' => 'color',
                'renderer' => fn (string $color) => "<div class='rounded' style='height: 20px; width: 40px; background-color: {$color}'>&nbsp;</div>"
            ])
            ->addColumn('actions', [
                'field' => 'id',
                'label' => '',
                'sortable' => false,
                'searchable' => false,
                'renderer' => $this->renderActions(...),
            ])
        ;
    }

    private function renderActions(int $id, Calendar $calendar): string
    {
        if (!$this->security->isGranted('forumify.admin.settings.calendars.manage')) {
            return '';
        }

        $actions = '';
        $actions .= $this->renderAction('forumify_admin_calendars_edit', ['identifier' => $id], 'pencil-simple-line');
        $actions .= $this->renderAction('forumify_admin_acl', (array)$calendar->getACLParameters(), 'lock-simple');
        $actions .= $this->renderAction('forumify_admin_calendars_delete', ['identifier' => $id], 'x');

        return $actions;
    }
}
