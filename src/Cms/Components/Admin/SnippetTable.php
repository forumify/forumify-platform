<?php

declare(strict_types=1);

namespace Forumify\Cms\Components\Admin;

use Forumify\Cms\Entity\Snippet;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('SnippetTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.cms.snippets.view')]
class SnippetTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

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

        $editUrl = $this->urlGenerator->generate('forumify_admin_cms_snippet_edit', ['slug' => $slug]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_cms_snippet_delete', ['slug' => $slug]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
