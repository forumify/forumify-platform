<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Forumify\Core\Entity\ACL;
use Forumify\Core\Entity\SortableEntityInterface;
use Forumify\Core\Entity\User;
use Forumify\Core\Event\EntityPostRemoveEvent;
use Forumify\Core\Event\EntityPostSaveEvent;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template T of object
 * @extends ServiceEntityRepository<T>
 */
abstract class AbstractRepository extends ServiceEntityRepository
{
    private EventDispatcherInterface $eventDispatcher;
    protected Security $security;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, static::getEntityClass());
    }

    #[Required]
    public function setServices(
        EventDispatcherInterface $eventDispatcher,
        Security $security,
    ): void {
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
    }

    protected function getEntityName(): string
    {
        return static::getEntityClass();
    }

    /**
     * @return class-string<T>
     */
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
            $this->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param array<object> $entities
     * @param bool $flush
     */
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
            $this->flush();
        }
    }

    /**
     * @param array<object> $entities
     * @param bool $flush
    */
    public function removeAll(array $entities, bool $flush = true): void
    {
        foreach ($entities as $entity) {
            $this->remove($entity, false);
        }

        if ($flush) {
            $this->flush();
        }
    }

    public function reorder(
        SortableEntityInterface $entity,
        string $direction,
        ?callable $queryMutator = null
    ): void {
        $predicate = $direction === 'up' ? '<' : '>';
        $qb = $this->createQueryBuilder('e')
            ->where("e.position $predicate :position")
            ->setParameter('position', $entity->getPosition())
            ->orderBy('e.position', $direction === 'up' ? 'DESC' : 'ASC')
            ->setMaxResults(1);

        if ($queryMutator !== null) {
            $queryMutator($qb);
        }

        try {
            $toSwap = $qb->getQuery()->getSingleResult();
        } catch (NoResultException|NonUniqueResultException) {
            return;
        }

        if (!$toSwap) {
            return;
        }

        $oldPosition = $entity->getPosition();
        $newPosition = $toSwap->getPosition();
        if ($newPosition === $oldPosition) {
            $newPosition += $direction === 'up' ? -1 : 1;
        }

        $toSwap->setPosition($oldPosition);
        $entity->setPosition($newPosition);

        $this->saveAll([$entity, $toSwap]);
    }

    /**
     * @param T $entity
     */
    public function getHighestPosition(object $entity): int
    {
        try {
            return (int) $this
                ->createQueryBuilder('e')
                ->select('MAX(e.position)')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        } catch (Exception) {
            return 0;
        }
    }

    protected function addACLToQuery(
        QueryBuilder $qb,
        string $permission,
        ?string $entity = null,
        ?string $alias = 'e',
        ?string $identifier = 'id',
    ): QueryBuilder {
        if ($this->security->isGranted(VoterAttribute::SuperAdmin->value)) {
            return $qb;
        }

        $entity ??= $this->getEntityName();
        $qb->innerJoin(ACL::class, 'acl', 'WITH', "acl.entity = :entity AND acl.entityId = $alias.$identifier AND acl.permission = :permission")
            ->innerJoin('acl.roles', 'acl_role')
            ->setParameter('permission', $permission)
            ->setParameter('entity', $entity);

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $qb->leftJoin('acl_role.users', 'acl_role_users')
                ->andWhere($qb->expr()->orX(
                    'acl_role_users.id = :userId',
                    'acl_role.slug = :userRole'
                ))
                ->setParameter('userId', $user->getId())
                ->setParameter('userRole', 'user');
        } else {
            $qb->andWhere('acl_role.slug = :guestRole')
                ->setParameter('guestRole', 'guest');
        }

        return $qb;
    }
}
