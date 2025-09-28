<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Twig\Environment;

#[AsLiveComponent('UserTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.users.view')]
class UserTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly Environment $twig,
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
                'field' => 'email',
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
        $user = $this->repository->find($id);
        if ($user === null) {
            return;
        }

        if (!$this->security->isGranted(VoterAttribute::UserBan->value, $user)) {
            throw new AccessDeniedException();
        }

        $user->setBanned(!$user->isBanned());
        $user->setRoleEntities([]);

        $this->repository->save($user);
    }

    protected function renderActionColumn(int $id, User $user): string
    {
        return $this->twig->render('@Forumify/admin/user/list/actions.html.twig', [
            'user' => $user,
        ]);
    }
}
