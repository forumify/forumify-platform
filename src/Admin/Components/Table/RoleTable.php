<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\Role;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('RoleTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.roles.view')]
class RoleTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    protected function getEntityClass(): string
    {
        return Role::class;
    }

    protected function buildTable(): void
    {
        $this
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
