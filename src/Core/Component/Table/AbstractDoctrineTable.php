<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Table;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractDoctrineTable extends AbstractTable
{
    public function __construct(protected readonly ServiceEntityRepository $repository)
    {
    }

    protected function getData(int $limit, int $offset, array $search, array $sort): array
    {
        $qb = $this->getQuery($search)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        foreach ($sort as $column => $direction) {
            $qb->addOrderBy("e.$column", $direction);
        }

        return $qb->getQuery()->getResult();
    }

    protected function getTotalCount(array $search): int
    {
        return $this->getQuery($search)
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getQuery(array $search): QueryBuilder
    {
        $qb = $this->repository->createQueryBuilder('e');
        foreach ($search as $column => $value) {
            $qb->andWhere("e.$column LIKE :$column");
            $qb->setParameter($column, "%$value%");
        }

        return $qb;
    }
}
