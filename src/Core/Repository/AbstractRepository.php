<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Forumify\Core\Event\EntityPostRemoveEvent;
use Forumify\Core\Event\EntityPostSaveEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractRepository extends ServiceEntityRepository
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, static::getEntityClass());
    }

    #[Required]
    public function setServices(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
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

        $this->eventDispatcher->dispatch(
            new EntityPostSaveEvent($entity),
            EntityPostSaveEvent::getName(static::getEntityClass())
        );

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
            $this->flush();
        }
    }

    public function remove(object $entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);

        $this->eventDispatcher->dispatch(
            new EntityPostRemoveEvent($entity),
            EntityPostRemoveEvent::getName(static::getEntityClass())
        );

        if ($flush) {
            $em->flush();
        }
    }

    public function removeAll(array $entities, bool $flush = true): void
    {
        foreach ($entities as $entity) {
            $this->remove($entity, false);
        }

        if ($flush) {
            $this->flush();
        }
    }
}
