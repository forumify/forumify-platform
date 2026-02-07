<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Entity\AuditLog;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

/**
 * @extends AbstractDoctrineList<AuditLog>
 */
#[AsLiveComponent('AuditLogEntityList', '@Forumify/admin/components/audit_log/list.html.twig')]
#[IsGranted('forumify.admin.settings.audit_logs.view')]
class AuditLogEntityList extends AbstractDoctrineList
{
    #[LiveProp]
    public string $class;

    #[LiveProp]
    public string $identifier;

    protected function getEntityClass(): string
    {
        return AuditLog::class;
    }

    protected function getQuery(): QueryBuilder
    {
        return parent::getQuery()
            ->andWhere('e.targetEntityClass = :cls')
            ->andWhere('e.targetEntityId = :id')
            ->setParameter('cls', $this->class)
            ->setParameter('id', $this->identifier)
            ->orderBy('e.uid', 'DESC')
        ;
    }
}
