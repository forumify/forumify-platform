<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Scheduler;

use DateInterval;
use DateTimeImmutable;
use Forumify\Core\Repository\AuditLogRepository;
use Forumify\Core\Scheduler\RemoveOldAuditLogsTask;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;
use Tests\Tests\Factories\Core\AuditLogFactory;
use Zenstruck\Foundry\Test\Factories;

class RemoveOldAuditLogsTaskTest extends KernelTestCase
{
    use Factories;

    public function testRemoveOldAuditLogs(): void
    {
        $old = AuditLogFactory::createOne([
            'action' => 'delete',
        ]);
        $oldUlidTime = new DateTimeImmutable()->sub(new DateInterval('P1Y1D'));
        $old->uid = new Ulid(Ulid::generate($oldUlidTime));
        AuditLogFactory::createOne(['action' => 'keep']);

        (self::getContainer()->get(RemoveOldAuditLogsTask::class))();

        $remainingLogs = self::getContainer()->get(AuditLogRepository::class)->findAll();

        self::assertCount(1, $remainingLogs);
        self::assertEquals('keep', $remainingLogs[0]->action);
    }
}
