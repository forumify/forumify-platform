<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Forumify\Core\Component\Table\AbstractDoctrineTable;
use Forumify\Core\Entity\AuditLog;
use Forumify\Core\Twig\Extension\CoreRuntime;
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
                'field' => 'user?.displayName',
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

    private function renderActions($uid, AuditLog $log): string
    {
        return $this->twig->render('@Forumify/admin/components/audit_log/details.html.twig', [
            'log' => $log,
        ]);
    }
}
