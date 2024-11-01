<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[IsGranted('forumify.admin.users.view')]
#[AsLiveComponent('UserTable', '@Forumify/components/table/table.html.twig')]
class UserTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
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

    protected function renderActionColumn(int $id): string
    {
        if (!$this->security->isGranted('forumify.admin.users.manage')) {
            return '';
        }

        $editUrl = $this->urlGenerator->generate('forumify_admin_users_edit', ['identifier' => $id]);
        $deleteUrl = $this->urlGenerator->generate('forumify_admin_users_delete', ['identifier' => $id]);

        return "
            <a class='btn-link btn-icon btn-small' href='$editUrl'><i class='ph ph-pencil-simple-line'></i></a>
            <a class='btn-link btn-icon btn-small' href='$deleteUrl'><i class='ph ph-x'></i></a>
        ";
    }
}
