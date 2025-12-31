<?php

declare(strict_types=1);

namespace Forumify\Core\Scheduler;

use DateTime;
use Forumify\Core\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask('6 hours', jitter: 120)]
#[AsCommand('forumify:platform:remove-users')]
class RemoveUserTask
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(): int
    {
        $unverifiedUsers = $this->userRepository->createQueryBuilder('u')
            ->where('u.emailVerified = 0')
            ->andWhere('u.email IS NOT NULL')
            ->andWhere('u.createdAt < :threshold')
            ->setParameter('threshold', new DateTime('-2 days'))
            ->getQuery()
            ->getResult();

        $this->userRepository->removeAll($unverifiedUsers);

        return Command::SUCCESS;
    }
}
