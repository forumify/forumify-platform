<?php

declare(strict_types=1);

namespace Forumify\Cms\Components\Admin;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Repository\PageRepository;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Component\Table\AbstractTable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('PageTable', '@Forumify/components/table/table.html.twig')]
class PageTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        PageRepository $repository,
    ) {
        parent::__construct($repository);
        $this->sort = ['title' => AbstractTable::SORT_ASC];
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('title', [
                'field' => 'title',
            ])
            ->addColumn('urlKey', [
                'field' => 'urlKey',
                'renderer' => $this->renderUrlKey(...),
            ])
            ->addColumn('actions', [
                'label' => '',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderActionColumn(...),
            ]);
    }

    private function renderUrlKey(string $urlKey): string
    {
        $url = $this->urlGenerator->generate('forumify_cms_page', ['urlKey' => $urlKey]);
        return "$urlKey <a href='$url' target='_blank'><i class='ph ph-arrow-square-out'></i></a>";
    }

    private function renderActionColumn($_, Page $page): string
    {
        $editUrl = $this->urlGenerator->generate('forumify_admin_cms_page_edit', ['slug' => $page->getSlug()]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_cms_page_delete', ['slug' => $page->getSlug()]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
