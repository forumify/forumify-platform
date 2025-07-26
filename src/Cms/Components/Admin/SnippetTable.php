<?php

declare(strict_types=1);

namespace Forumify\Cms\Components\Admin;

use Forumify\Cms\Entity\Snippet;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('SnippetTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.cms.snippets.view')]
class SnippetTable extends AbstractDoctrineTable
{
    protected function getEntityClass(): string
    {
        return Snippet::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('name', [
                'field' => 'name',
            ])
            ->addColumn('slug', [
                'field' => 'slug',
            ])
            ->addColumn('actions', [
                'field' => 'slug',
                'label' => '',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderActionColumn(...),
            ]);
    }

    private function renderActionColumn(string $slug): string
    {
        if (!$this->security->isGranted('forumify.admin.cms.snippets.manage')) {
            return '';
        }

        $actions = '';
        $actions .= $this->renderAction('forumify_admin_cms_snippet_edit', ['slug' => $slug], 'pencil-simple-line');
        $actions .= $this->renderAction('forumify_admin_cms_snippet_delete', ['slug' => $slug], 'x');
        return $actions;
    }
}
