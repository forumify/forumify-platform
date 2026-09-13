<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Forum\Entity\Reaction;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('ReactionTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.reactions.view')]
class ReactionTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly Packages $packages,
        private readonly CacheManager $liip,
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
                'renderer' => $this->renderName(...),
            ])
            ->addActionColumn($this->renderActionColumn(...))
        ;
    }

    private function renderName(string $name, Reaction $reaction): string
    {
        $reactionImg = $this->packages->getUrl($reaction->image, 'forumify.asset');
        $img = $this->liip->getBrowserPath($reactionImg, 'reaction');
        return "<div class='flex items-center gap-2'><img class='reaction' src='$img'><span>$name</span></div>";
    }

    private function renderActionColumn(int $id): string
    {
        if (!$this->security->isGranted('forumify.admin.settings.reactions.manage')) {
            return '';
        }

        $actions = '';
        $actions .= $this->renderAction('forumify_admin_reactions_edit', ['identifier' => $id], 'pencil-simple-line');
        $actions .= $this->renderAction('forumify_admin_reactions_delete', ['identifier' => $id], 'x');

        return $actions;
    }
}
