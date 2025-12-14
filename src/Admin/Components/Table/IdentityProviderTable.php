<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\OAuth\Entity\IdentityProvider;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\IdentityProviderTable', '@Forumify/components/table/table.html.twig')]
class IdentityProviderTable extends AbstractDoctrineTable
{
    protected function getEntityClass(): string
    {
        return IdentityProvider::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('name', [
                'field' => 'name',
            ])
            ->addColumn('type', [
                'field' => 'type',
            ])
            ->addActionColumn($this->renderActions(...))
        ;
    }

    private function renderActions(int $id): string
    {
        if (!$this->security->isGranted('forumify.admin.settings.identity_providers.manage')) {
            return '';
        }

        $actions = '';
        $actions .= $this->renderAction('forumify_admin_identity_providers_edit', ['identifier' => $id], 'pencil-simple-line');
        $actions .= $this->renderAction('forumify_admin_identity_providers_delete', ['identifier' => $id], 'x');
        return $actions;
    }
}
