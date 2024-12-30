<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Forum\Entity\Badge;
use Forumify\Forum\Repository\BadgeRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;

#[AsLiveComponent('BadgeTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.badges.view')]
class BadgeTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly BadgeRepository $badgeRepository,
        private readonly Security $security,
    ) {
        $this->sort = ['position' => self::SORT_ASC];
    }

    protected function getEntityClass(): string
    {
        return Badge::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('position', [
                'label' => '#',
                'field' => 'id',
                'renderer' => $this->renderSortColumn(...),
                'searchable' => false,
                'class' => 'w-10',
            ])
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

    #[LiveAction]
    #[IsGranted('forumify.admin.settings.badges.manage')]
    public function reorder(#[LiveArg] int $id, #[LiveArg] string $direction): void
    {
        $badge = $this->badgeRepository->find($id);
        if ($badge === null) {
            return;
        }

        $this->badgeRepository->reorder($badge, $direction);
    }

    protected function renderActionColumn(int $id): string
    {
        if (!$this->security->isGranted('forumify.admin.settings.badges.manage')) {
            return '';
        }

        $editUrl = $this->urlGenerator->generate('forumify_admin_badges_edit', ['identifier' => $id]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_badges_delete', ['identifier' => $id]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }

    protected function renderSortColumn(int $id): string
    {
        if (!$this->security->isGranted('forumify.admin.settings.badges.manage')) {
            return '';
        }

        return '
            <button
                class="btn-link btn-small btn-icon p-1"
                data-action="live#action"
                data-live-action-param="reorder"
                data-live-id-param="' . $id . '"
                data-live-direction-param="down"
            >
                <i class="ph ph-arrow-down"></i>
            </button>
            <button
                class="btn-link btn-small btn-icon p-1"
                data-action="live#action"
                data-live-action-param="reorder"
                data-live-id-param="' . $id . '"
                data-live-direction-param="up"
            >
                <i class="ph ph-arrow-up"></i>
            </button>';
    }
}
