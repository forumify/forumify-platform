<?php

declare(strict_types=1);

namespace Forumify\Core\Component\List;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Repository\AbstractRepository;
use RuntimeException;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractDoctrineList extends AbstractList
{
    protected AbstractRepository $repository;
    private array $identifiers = [];

    abstract protected function getEntityClass(): string;

    protected function getData(): array
    {
        $limit = $this->limit;
        $offset = ($this->page - 1) * $limit;

        $qb = $this->getQuery()
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    protected function getTotalCount(): int
    {
        $ids = implode(',', array_map(static fn (string $id) => "e.$id", $this->identifiers));
        return $this->getQuery()
            ->select("COUNT($ids)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getQuery(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('e');
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
