<?php

declare(strict_types=1);

namespace Forumify\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Core\Security\VoterAttribute;
use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use Symfony\Bundle\SecurityBundle\Security;
use Throwable;

class AccessControlListExtension implements QueryCollectionExtensionInterface
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
        if (!$operation) {
            return;
        }

        $this->addWhere($queryBuilder, $operation, $resourceClass);
    }

    /**
     * @param class-string $resourceClass
     */
    private function addWhere(QueryBuilder $qb, Operation $operation, string $resourceClass): void
    {
        $acl = $operation->getExtraProperties()['acl'] ?? null;
        if ($acl === null) {
            return;
        }

        if ($this->security->isGranted(VoterAttribute::SuperAdmin->value)) {
            return;
        }

        $rootAlias = $qb->getRootAliases()[0] ?? null;
        assert($rootAlias !== null);

        if (is_array($acl)) {
            ['permission' => $permission, 'entity' => $path] = $acl;
            $path = explode('.', $path);

            $alias = $this->addJoins($qb, $rootAlias, $path);
            $resourceClass = $this->getEntityClassAtPath($resourceClass, $path);
        } else {
            $permission = $acl;
            $alias = $rootAlias;
        }

        $idField = $this->em->getClassMetadata($resourceClass)->getIdentifier()[0] ?? 'id';

        // We can't add ACL to the query directly because of some API-Platform fuckery
        // in their pagination extension,... so we add a WHERE IN instead.
        $accessible = $this->getAccess($resourceClass, $idField, $permission);
        if (empty($accessible)) {
            $qb->andWhere('1 = 0');
            return;
        }

        $qb->andWhere("$alias.$idField IN (:acl_entity_ids)")
            ->setParameter('acl_entity_ids', $accessible);
    }

    /**
     * @param list<string> $path
     */
    private function addJoins(QueryBuilder $qb, string $rootAlias, array $path): string
    {
        $i = 0;
        $lastAlias = $rootAlias;
        while ($part = array_shift($path)) {
            $alias = 'acl_join_' . ++$i;
            $qb->leftJoin("$lastAlias.$part", $alias);

            $lastAlias = $alias;
        }

        return $lastAlias;
    }

    /**
     * @param class-string $class
     * @param list<string> $path
     * @return class-string
     */
    private function getEntityClassAtPath(string $class, array $path): string
    {
        $prop = array_shift($path);
        if (!$prop) {
            return $class;
        }

        $refl = new ReflectionClass($class);
        $prop = $refl->getProperty($prop);
        $type = $prop->getType();
        if (!$type instanceof ReflectionNamedType) {
            throw new LogicException("{$prop->getName()} is not a named type.");
        }

        $type = $type->getName();
        if (!class_exists($type)) {
            throw new LogicException("{$type} is not a known class.");
        }

        return $this->getEntityClassAtPath($type, $path);
    }

    /**
     * @param class-string $class
     * @return array<mixed>
     */
    private function getAccess(string $class, string $idField, string $permission): array
    {
        $repository = $this->em->getRepository($class);
        if (!$repository instanceof AbstractRepository) {
            return [];
        }

        $qb = $repository->createQueryBuilder('e')->select("e.$idField");
        $repository->addACLToQuery($qb, $permission, $class);

        try {
            return $qb->getQuery()->getSingleColumnResult();
        } catch (Throwable) {
            return [];
        }
    }
}
