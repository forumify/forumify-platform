<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;

#[AsLiveComponent('RoleTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.roles.view')]
class RoleTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RoleRepository $roleRepository,
        private readonly Security $security,
    ) {
        $this->sort = ['position' => self::SORT_ASC];
    }

    protected function getEntityClass(): string
    {
        return Role::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('position', [
                'label' => '#',
                'field' => 'id',
                'renderer' => [$this, 'renderSortColumn'],
                'searchable' => false,
                'class' => 'w-10',
            ])
            ->addColumn('title', [
                'field' => 'title',
                'renderer' => [$this, 'renderTitleColumn'],
            ])
            ->addColumn('actions', [
                'label' => '',
                'field' => 'id',
                'searchable' => false,
                'sortable' => false,
                'renderer' => [$this, 'renderActionColumn'],
            ]);
    }

    #[LiveAction]
    #[IsGranted('forumify.admin.settings.roles.manage')]
    public function reorder(#[LiveArg] int $id, #[LiveArg] string $direction): void
    {
        $role = $this->roleRepository->find($id);
        if ($role === null) {
            return;
        }

        $predicate = $direction === 'up' ? '<' : '>';
        $siblings = $this->roleRepository->createQueryBuilder('r')
            ->where("r.position $predicate :position")
            ->setParameter('position', $role->getPosition())
            ->andWhere('r.system = 0')
            ->orderBy('r.position', $direction === 'up' ? 'DESC' : 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        $toSwap = reset($siblings);
        if ($toSwap === false) {
            return;
        }

        $oldPosition = $role->getPosition();
        $newPosition = $toSwap->getPosition();
        if ($newPosition === $oldPosition) {
            $newPosition += $direction === 'up' ? -1 : 1;
        }

        $toSwap->setPosition($oldPosition);
        $role->setPosition($newPosition);

        $this->roleRepository->saveAll([$role, $toSwap]);
    }

    protected function renderSortColumn(int $id, Role $role): string
    {
        if ($role->isSystem() || !$this->security->isGranted('forumify.admin.settings.roles.manage')) {
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

    protected function renderTitleColumn(string $title, Role $role): string
    {
        return "
            <p>$title</p>
            <p class='text-small'>{$role->getDescription()}</p>
        ";
    }

    protected function renderActionColumn(int $id, Role $role): string
    {
        if ($role->isSystem() || !$this->security->isGranted('forumify.admin.settings.roles.manage')) {
            return '';
        }

        $editUrl = $this->urlGenerator->generate('forumify_admin_roles_edit', ['identifier' => $id]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_roles_delete', ['identifier' => $id]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
