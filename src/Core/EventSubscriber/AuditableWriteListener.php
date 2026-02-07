<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Forumify\Core\Entity\AuditLog;
use Forumify\Core\Repository\AuditLogRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::TERMINATE, method: 'writeLogs')]
class AuditableWriteListener
{
    /** @var array<AuditLog> */
    private array $logs = [];

    public function __construct(
        private readonly AuditLogRepository $auditLogRepository,
    ) {
    }

    public function queueLog(AuditLog $log): void
    {
        $this->logs[] = $log;
    }

    public function writeLogs(): void
    {
        $this->auditLogRepository->saveAll($this->logs);
    }
}
