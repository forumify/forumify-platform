<?php

declare(strict_types=1);

namespace Forumify\Cms\Components\Admin;

use Forumify\Cms\Entity\Snippet;
use Forumify\Cms\Repository\SnippetRepository;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('SnippetTable', '@Forumify/components/table/table.html.twig')]
class SnippetTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        SnippetRepository $repository
    ) {
        parent::__construct($repository);
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('name', [
                'field' => 'name'
            ])
            ->addColumn('slug', [
                'field' => 'slug'
            ])
            ->addColumn('actions', [
                'label' => '',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderActionColumn(...),
            ]);
    }

    private function renderActionColumn($_, Snippet $snippet): string
    {
        $editUrl = $this->urlGenerator->generate('forumify_admin_cms_snippet_edit', ['slug' => $snippet->getSlug()]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_cms_snippet_delete', ['slug' => $snippet->getSlug()]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
