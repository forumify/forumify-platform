<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Component\Table\AbstractTable;
use Forumify\Core\Entity\Theme;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\ThemeTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.themes.view')]
class ThemeTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
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
        if (!$this->security->isGranted('forumify.admin.settings.themes.manage')) {
            return '';
        }

        $actions = '';
        $actions .= $this->renderAction('forumify_admin_themes_edit', ['identifier' => $id], 'pencil-simple-line');
        $actions .= $this->renderAction('forumify_admin_themes_templates', ['id' => $id], 'file-html');

        return $actions;
    }
}
