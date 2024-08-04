<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Api\Entity\OAuthClient;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('OAuthClientTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.api.manage')]
class OAuthClientTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    protected function getEntityClass(): string
    {
        return OAuthClient::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('clientId', [
                'field' => 'clientId',
            ])
            ->addColumn('actions', [
                'label' => '',
                'field' => 'id',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderActionColumn(...),
            ]);
    }

    private function renderActionColumn(int $id): string
    {
        $editUrl = $this->urlGenerator->generate('forumify_admin_api_clients_edit', ['identifier' => $id]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_api_clients_delete', ['identifier' => $id]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
