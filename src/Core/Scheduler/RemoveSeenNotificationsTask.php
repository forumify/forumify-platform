<?php

declare(strict_types=1);

namespace Forumify\Core\Scheduler;

use DateInterval;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask('12 hours', jitter: 120)]
class RemoveSeenNotificationsTask
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(): void
    {
        $toKeepIds = '
            SELECT id FROM (
                SELECT n2.id
                FROM notification n2
                WHERE n2.recipient_id = n.recipient_id
                ORDER BY n2.seen ASC, n2.created_at DESC
                LIMIT 10
            ) AS toKeep
        ';

        $this->entityManager->getConnection()->executeStatement("
            DELETE n FROM notification n
            WHERE (n.seen = 1 AND n.id NOT IN ($toKeepIds)) OR n.created_at < :minKeep
        ", [
            'minKeep' => new DateTime()->sub(new DateInterval('P1Y')),
        ], [
            'minKeep' => Types::DATETIME_MUTABLE,
        ]);
    }
}
