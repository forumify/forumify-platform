<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Component\Table\AbstractTable;
use Forumify\Core\Entity\Theme;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\ThemeTable', '@Forumify/components/table/table.html.twig')]
class ThemeTable extends AbstractDoctrineTable
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
        $this->sort = [
            'active' => AbstractTable::SORT_DESC,
            'name' => AbstractTable::SORT_DESC,
        ];
    }

    protected function getEntityClass(): string
    {
        return Theme::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('name', [
                'field' => 'name',
            ])
            ->addColumn('plugin', [
                'field' => 'plugin',
                'searchable' => false,
                'sortable' => false,
                'renderer' => fn (Plugin $plugin) => $plugin->getPlugin()->getPluginMetadata()->name,
            ])
            ->addColumn('active', [
                'field' => 'active',
                'searchable' => false,
                'renderer' => fn (bool $active) => $active ? '<i class="ph ph-check"></i>' : '<i class="ph ph-x"></i>',
            ])
            ->addColumn('actions', [
                'label' => '',
                'field' => 'id',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderActions(...),
            ]);
    }

    private function renderActions(int $id): string
    {
        $editUrl = $this->urlGenerator->generate('forumify_admin_themes_edit', ['identifier' => $id]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
        ";
    }
}
