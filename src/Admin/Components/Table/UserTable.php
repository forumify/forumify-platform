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
        UserRepository $userRepository
    ) {
        parent::__construct($userRepository);
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('username', [
                'field' => 'username',
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
        return '<a class="btn-link btn-icon btn-small" href="' . $editUrl . '"><i class="ph ph-pencil-simple-line"></i></a>';
    }
}
