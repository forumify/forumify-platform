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

    abstract protected function getEntityClass(): string;

    protected function getData(): array
    {
        $search = array_filter($this->search);
        $sort = array_filter($this->sort);

        $limit = $this->limit;
        $offset = ($this->page - 1) * $limit;

        $qb = $this->getQuery($search)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        foreach ($sort as $column => $direction) {
            $qb->addOrderBy("e.$column", $direction);
        }

        return $qb->getQuery()->getResult();
    }

    protected function getTotalCount(): int
    {
        $search = array_filter($this->search);

        $ids = implode(',', array_map(static fn (string $id) => "e.$id", $this->identifiers));
        return $this->getQuery($search)
            ->select("COUNT($ids)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getQuery(array $search): QueryBuilder
    {
        $qb = $this->repository->createQueryBuilder('e');
        foreach ($search as $column => $value) {
            $qb->andWhere("e.$column LIKE :$column");
            $qb->setParameter($column, "%$value%");
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
}
