<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Core;

use Forumify\Core\Entity\AuditLog;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AuditLog>
 */
class AuditLogFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return AuditLog::class;
    }

    protected function defaults(): array|callable
    {
        return [];
    }
}
