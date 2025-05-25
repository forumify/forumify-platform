<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Twig\Environment;

#[IsGranted('forumify.admin.users.view')]
#[AsLiveComponent('UserTable', '@Forumify/components/table/table.html.twig')]
class UserTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly Environment $twig,
        private readonly UserRepository $userRepository,
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
                'field' => 'id',
                'searchable' => false,
                'sortable' => false,
                'renderer' => [$this, 'renderActionColumn'],
            ]);
    }

    #[LiveAction]
    #[IsGranted('forumify.admin.users.manage')]
    public function toggleBanned(#[LiveArg] int $id): void
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($id);
        if ($user === null) {
            return;
        }

        $user->setBanned(!$user->isBanned());
        $user->setRoleEntities([]);

        $this->userRepository->save($user);
    }

    protected function renderActionColumn(int $id, User $user): string
    {
        return $this->twig->render('@Forumify/admin/user/list/actions.html.twig', [
            'user' => $user,
        ]);
    }
}
