<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Table;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Repository\AbstractRepository;
use RuntimeException;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractDoctrineTable extends AbstractTable
{
    private AbstractRepository $repository;
    private array $identifiers = [];
    private array $aliases = [];

    abstract protected function getEntityClass(): string;

    protected function getData(int $limit, int $offset, array $search, array $sort): array
    {
        $qb = $this->getQuery($search)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        foreach ($sort as $column => $direction) {
            $qb = $this->addSortBy($qb, $column, $direction);
        }

        return $qb->getQuery()->getResult();
    }

    protected function getTotalCount(array $search): int
    {
        $ids = implode(',', array_map(static fn (string $id) => "e.$id", $this->identifiers));
        return $this->getQuery($search)
            ->select("COUNT($ids)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getQuery(array $search): QueryBuilder
    {
        $qb = $this->repository->createQueryBuilder('e');
        $this->addJoins($qb);

        foreach ($search as $column => $value) {
            $qb = $this->addSearch($qb, $column, $value);
        }

        return $qb;
    }

    #[Required]
    public function setServices(EntityManagerInterface $em): void
    {
        $repository = $em->getRepository($this->getEntityClass());
        if (!$repository instanceof AbstractRepository) {
            throw new RuntimeException('Your entity must have a repository that extends ' . AbstractRepository::class);
        }
        $this->repository = $repository;

        $this->identifiers = $em->getClassMetadata($this->getEntityClass())->getIdentifier();
        if (empty($this->identifiers)) {
            throw new RuntimeException('Your entity must have at least 1 identifier (#[ORM\Id])');
        }
    }

    private function addJoins(QueryBuilder $qb): QueryBuilder
    {
        foreach ($this->getColumns() as $columnName => $column) {
            $field = $column['field'] ?? $columnName;
            $property = explode('.', str_replace('?', '', $field));

            if (count($property) === 1) {
                $this->aliases[$columnName] = 'e.' . reset($property);
                continue;
            }

            if (count($property) === 2) {
                [$join, $property] = $property;
                $alias = uniqid($join . '_');
                $qb->leftJoin("e.$join", $alias);
                $this->aliases[$columnName] = "$alias.$property";
                continue;
            }

            throw new RuntimeException('Having properties nested deeper than 2 is not allowed.');
        }

        return $qb;
    }

    private function addSearch(QueryBuilder $qb, string $column, string $value): QueryBuilder
    {
        $alias = $this->aliases[$column];
        return $qb
            ->andWhere("$alias LIKE :value")
            ->setParameter('value', "%$value%")
        ;
    }

    private function addSortBy(QueryBuilder $qb, string $column, string $direction): QueryBuilder
    {
        $alias = $this->aliases[$column];
        return $qb->addOrderBy($alias, $direction);
    }
}
