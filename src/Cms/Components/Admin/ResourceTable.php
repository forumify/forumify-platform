<?php

declare(strict_types=1);

namespace Forumify\Cms\Components\Admin;

use Forumify\Cms\Entity\Resource;
use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('ResourceTable', '@Forumify/components/table/table.html.twig')]
class ResourceTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Packages $packages,
    ) {
    }

    protected function getEntityClass(): string
    {
        return Resource::class;
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
            ->addColumn('preview', [
                'field' => 'path',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderPreview(...),
            ])
            ->addColumn('actions', [
                'field' => 'slug',
                'label' => '',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderActionColumn(...),
            ]);
    }

    private function renderPreview(string $path, Resource $resource): string
    {
        $url = $this->packages->getUrl($path, 'forumify.resource');
        return "<a class='flex justify-center items-center' style='width: 64px; height: 64px' href='$url' target='_blank'>
            <img src='$url' width='100%' height='auto' alt='{$resource->getName()}'>
        </a>";
    }

    private function renderActionColumn(string $slug): string
    {
        $editUrl = $this->urlGenerator->generate('forumify_admin_cms_resource_edit', ['slug' => $slug]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_cms_resource_delete', ['slug' => $slug]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
