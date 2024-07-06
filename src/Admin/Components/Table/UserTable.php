<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('UserTable', '@Forumify/components/table/table.html.twig')]
class UserTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    protected function getEntityClass(): string
    {
        return User::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('username', [
                'field' => 'username',
            ])
            ->addColumn('displayName', [
                'field' => 'displayName',
                'label' => 'Display name',
            ])
            ->addColumn('email', [
                'field' => 'email'
            ])
            ->addColumn('actions', [
                'label' => '',
                'searchable' => false,
                'sortable' => false,
                'renderer' => [$this, 'renderActionColumn'],
            ]);
    }

    protected function renderActionColumn($_, User $user): string
    {
        $editUrl = $this->urlGenerator->generate('forumify_admin_user', ['username' => $user->getUsername()]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_user_delete', ['username' => $user->getUsername()]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
