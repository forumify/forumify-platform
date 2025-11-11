<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use DateTime;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Twig\Extension\CoreRuntime;
use Forumify\OAuth\Entity\OAuthClient;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\OAuthClientTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.oauth_clients.view')]
class OAuthClientTable extends AbstractDoctrineTable
{
    public function __construct(private readonly CoreRuntime $coreRuntime)
    {
    }

    protected function getEntityClass(): string
    {
        return OAuthClient::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('name', [
                'field' => 'name',
            ])
            ->addColumn('clientId', [
                'label' => 'Client ID',
                'field' => 'clientId',
            ])
            ->addColumn('lastActivity', [
                'label' => 'Last Used',
                'field' => 'lastActivity',
                'renderer' => fn (?DateTime $lastActivity) => $lastActivity !== null
                    ? $this->coreRuntime->formatDate($lastActivity)
                    : 'Never',
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
        if (!$this->isGranted('forumify.admin.settings.oauth_clients.manage')) {
            return '';
        }

        $actions = '';
        $actions .= $this->renderAction('forumify_admin_oauth_clients_edit', ['identifier' => $id], 'pencil-simple-line');
        $actions .= $this->renderAction('forumify_admin_oauth_clients_delete', ['identifier' => $id], 'x');

        return $actions;
    }
}
