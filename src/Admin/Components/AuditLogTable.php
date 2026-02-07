<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\AuditLog;
use Forumify\Core\Twig\Extension\CoreRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Twig\Environment;

use function Symfony\Component\String\u;

#[AsLiveComponent('AuditLogTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.settings.audit_logs.view')]
class AuditLogTable extends AbstractDoctrineTable
{
    public function __construct(
        private readonly CoreRuntime $coreRuntime,
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        $this->sort = ['uid' => 'DESC'];
    }

    protected function getEntityClass(): string
    {
        return AuditLog::class;
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('uid', [
                'field' => 'uid',
                'label' => 'Created',
                'searchable' => false,
                'renderer' => fn(Ulid $uid) => $this->coreRuntime->formatDate($uid->getDateTime()),
                'class' => 'w-15',
            ])
            ->addColumn('user', [
                'field' => 'user?.username',
                'renderer' => $this->renderUsername(...),
            ])
            ->addColumn('action', [
                'field' => 'action',
            ])
            ->addColumn('entity', [
                'field' => 'targetEntityClass',
                'renderer' => fn(?string $cls) => u($cls)->afterLast('\\')->lower()->toString(),
            ])
            ->addColumn('identifier', [
                'field' => 'targetEntityId',
            ])
            ->addColumn('name', [
                'field' => 'targetName',
            ])
            ->addActionColumn($this->renderActions(...), 'uid')
        ;
    }

    private function renderUsername(?string $username): string
    {
        if (empty($username)) {
            return 'System';
        }

        $profileUrl = $this->urlGenerator->generate('forumify_forum_profile', ['username' => $username]);
        return "<a href='$profileUrl' target='_blank'>$username</a>";
    }

    private function renderActions(Ulid $_, AuditLog $log): string
    {
        return $this->twig->render('@Forumify/admin/components/audit_log/details.html.twig', [
            'log' => $log,
        ]);
    }
}
