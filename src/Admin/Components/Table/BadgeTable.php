<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Forum\Entity\Badge;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('BadgeTable', '@Forumify/components/table/table.html.twig')]
class BadgeTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    protected function getEntityClass(): string
    {
        return Badge::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('name', [
                'field' => 'name',
            ])
            ->addColumn('actions', [
                'field' => 'id',
                'label' => '',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderActionColumn(...),
            ]);
    }

    protected function renderActionColumn(int $id): string
    {
        $editUrl = $this->urlGenerator->generate('forumify_admin_badge', ['id' => $id]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_badge_delete', ['id' => $id]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
