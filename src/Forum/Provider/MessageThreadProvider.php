<?php

declare(strict_types=1);

namespace Forumify\Forum\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Forumify\Core\Entity\User;
use Forumify\Forum\Repository\MessageThreadRepository;
use Symfony\Bundle\SecurityBundle\Security;

class MessageThreadProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly MessageThreadRepository $messageThreadRepository,
    ) {
    }

    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): object|array|null {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return [];
        }

        return $this->messageThreadRepository->createQueryBuilder('e')
            ->select('e, MAX(m.createdAt) AS HIDDEN maxCreatedAt')
            ->leftJoin('e.messages', 'm')
            ->join('e.participants', 'p')
            ->where('p = (:user)')
            ->setParameter('user', $user)
            ->groupBy('e.id')
            ->orderBy('maxCreatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
