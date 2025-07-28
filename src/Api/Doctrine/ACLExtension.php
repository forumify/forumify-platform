<?php

declare(strict_types=1);

namespace Forumify\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Bundle\SecurityBundle\Security;

class ACLExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->addWhere($queryBuilder, $operation, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->addWhere($queryBuilder, $operation, $resourceClass);
    }

    private function addWhere(QueryBuilder $qb, ?Operation $operation, string $resourceClass): void
    {
        if ($operation === null || !is_a($resourceClass, AccessControlledEntityInterface::class, true)) {
            return;
        }

        $acl = $operation->getExtraProperties()['acl'] ?? [];
        $permission = $acl['permission'] ?? null;
        if ($permission === null) {
            return;
        }

        if ($this->security->isGranted(VoterAttribute::SuperAdmin->value)) {
            return;
        }

        $repository = $this->em->getRepository($resourceClass);
        if (!$repository instanceof AbstractRepository) {
            return;
        }

        $rootAlias = $qb->getRootAliases()[0] ?? null;
        assert($rootAlias !== null);

        // TODO: It's fucked! https://github.com/api-platform/core/issues/7320
        // $repository->addACLToQuery($qb, $permission, $resourceClass, $rootAlias);
    }
}
