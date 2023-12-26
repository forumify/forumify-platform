<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Forum\Entity\Reaction;
use Forumify\Forum\Repository\ReactionRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('ReactionTable', '@Forumify/components/table/table.html.twig')]
class ReactionTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        ReactionRepository $reactionRepository,
    ) {
        parent::__construct($reactionRepository);
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn([
                'name' => 'name',
                'field' => 'name',
            ])
            ->addColumn([
                'name' => 'actions',
                'label' => '',
                'searchable' => false,
                'sortable' => false,
                'renderer' => [$this, 'renderActionColumn'],
            ]);
    }

    protected function renderActionColumn($_, Reaction $reaction): string
    {
        $editUrl = $this->urlGenerator->generate('forumify_admin_reaction', ['id' => $reaction->getId()]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_reaction_delete', ['id' => $reaction->getId()]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
