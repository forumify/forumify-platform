<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Forum\Entity\Reaction;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('ReactionTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.reactions.view')]
class ReactionTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    protected function getEntityClass(): string
    {
        return Reaction::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('name', [
                'field' => 'name',
            ])
            ->addColumn('actions', [
                'label' => '',
                'field' => 'id',
                'searchable' => false,
                'sortable' => false,
                'renderer' => [$this, 'renderActionColumn'],
            ]);
    }

    protected function renderActionColumn(int $id): string
    {
        if (!$this->security->isGranted('forumify.admin.settings.reactions.manage')) {
            return '';
        }

        $editUrl = $this->urlGenerator->generate('forumify_admin_reactions_edit', ['identifier' => $id]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_reactions_delete', ['identifier' => $id]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
