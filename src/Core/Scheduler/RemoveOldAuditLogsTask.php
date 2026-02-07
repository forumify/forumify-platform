<?php

declare(strict_types=1);

namespace Forumify\Core\Scheduler;

use DateInterval;
use DateTimeImmutable;
use Forumify\Core\Repository\AuditLogRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Uid\Ulid;

#[AsCronTask('@midnight', jitter: 1800)]
#[AsCommand('forumify:platform:prune-audit')]
class RemoveOldAuditLogsTask
{
    public function __construct(
        private readonly AuditLogRepository $auditLogRepository,
    ) {
    }

    public function __invoke(): int
    {
        $cutoff = new DateTimeImmutable()->sub(new DateInterval('P1Y'));
        $cutoffUid = new Ulid(Ulid::generate($cutoff))->toBinary();

        $this->auditLogRepository->createQueryBuilder('al')
            ->delete()
            ->where('al.uid < :cutoff')
            ->setParameter('cutoff', $cutoffUid)
            ->getQuery()
            ->execute()
        ;

        return Command::SUCCESS;
    }
}
