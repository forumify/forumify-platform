<?php

declare(strict_types=1);

namespace Forumify\Forum\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Forumify\Core\Entity\AuthorizableInterface;
use Forumify\Forum\Entity\MessageThread;
use Symfony\Bundle\SecurityBundle\Security;

class MessageThreadExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function applyToCollection(QueryBuilder $qb, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ($resourceClass !== MessageThread::class) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof AuthorizableInterface) {
            $qb->andWhere('1 = 0');
            return;
        }

        $rootAlias = $qb->getRootAliases()[0] ?? 'e';
        $qb
            ->andWhere(":user MEMBER OF $rootAlias.participants")
            ->setParameter('user', $user->getUser())
            ->addOrderBy("$rootAlias.lastMessageAt", 'DESC')
        ;
    }
}
