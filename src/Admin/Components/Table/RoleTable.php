<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('RoleTable', '@Forumify/components/table/table.html.twig')]
class RoleTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        RoleRepository $roleRepository
    ) {
        parent::__construct($roleRepository);
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn([
                'name' => 'title',
                'field' => 'title',
                'renderer' => [$this, 'renderTitleColumn'],
            ])
            ->addColumn([
                'name' => 'actions',
                'label' => '',
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

    protected function renderActionColumn($_, Role $role): string
    {
        if ($role->isSystem()) {
            return '';
        }

        $editUrl = $this->urlGenerator->generate('forumify_admin_reaction', ['id' => $role->getId()]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_role_delete', ['id' => $role->getId()]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
