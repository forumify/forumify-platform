<?php

declare(strict_types=1);

namespace Forumify\Core\Scheduler;

use DateTime;
use Forumify\Core\Repository\UserRepository;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask('6 hours')]
class RemoveUserTask
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(): void
    {
        $unverifiedUsers = $this->userRepository->createQueryBuilder('u')
            ->where('u.emailVerified = 0')
            ->andWhere('u.createdAt < :threshold')
            ->setParameter('threshold', new DateTime('-2 days'))
            ->getQuery()
            ->getResult();

        foreach ($unverifiedUsers as $user) {
            $this->userRepository->remove($user, false);
        }
        $this->userRepository->flush();
    }
}
