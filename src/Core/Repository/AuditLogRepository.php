<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Core\Entity\AuditLog;

/**
 * @extends AbstractRepository<AuditLog>
 */
class AuditLogRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return AuditLog::class;
    }
}
