<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Twig\Environment;

#[IsGranted('forumify.admin.roles.view')]
#[AsLiveComponent('Forumify\\UsersInRoleTable', '@Forumify/components/table/table.html.twig')]
class UsersInRoleTable extends UserTable
{
    #[LiveProp]
    public int $roleId;

    public function __construct(
        Environment $twig,
        private readonly Security $security,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct($twig, $userRepository);
    }

    #[LiveAction]
    #[IsGranted('forumify.admin.users.manage')]
    public function removeUser(#[LiveArg] int $userId): void
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            return;
        }

        $newRoles = $user->getRoleEntities()->filter(fn (Role $role) => $role->getId() !== $this->roleId);
        $user->setRoleEntities($newRoles);
        $this->userRepository->save($user);
    }

    protected function buildTable(): void
    {
        parent::buildTable();
        $this->addColumn('actions', [
            'label' => '',
            'field' => 'id',
            'searchable' => false,
            'sortable' => false,
            'renderer' => [$this, 'renderActionColumn'],
        ]);
    }

    protected function getQuery(array $search): QueryBuilder
    {
        return parent::getQuery($search)
            ->innerJoin('e.roles', 'r')
            ->where('r.id = :roleId')
            ->setParameter('roleId', $this->roleId);
    }

    protected function renderActionColumn(int $id, User $user): string
    {
        if (!$this->security->isGranted('forumify.admin.users.manage')) {
            return '';
        }

        return "
            <button
                type='button' 
                class='btn-link btn-icon btn-small'
                data-action='live#action'
                data-live-action-param='removeUser'
                data-live-user-id-param='$id'
            >
                <i class='ph ph-x'></i>
            </button>
        ";
    }
}
