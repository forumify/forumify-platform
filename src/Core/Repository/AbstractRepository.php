<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, static::getEntityClass());
    }

    protected function getEntityName(): string
    {
        return static::getEntityClass();
    }

    abstract public static function getEntityClass(): string;

    public function save(object $entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);

        if ($flush) {
            $em->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function saveAll(array $entities, bool $flush = true): void
    {
        foreach ($entities as $entity) {
            $this->save($entity, false);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(object $entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);

        if ($flush) {
            $em->flush();
        }
    }
}
