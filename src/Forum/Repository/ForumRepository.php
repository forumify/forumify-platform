<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;

class ForumRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Forum::class;
    }

    /**
     * @return array<Forum>
     */
    public function findByParent(?Forum $parent): array
    {
        return $this->findBy(['parent' => $parent], ['position' => 'ASC']);
    }

    /**
     * @return array<Forum>
     */
    public function findUngroupedByParent(?Forum $parent): array
    {
        return $this->findBy(['parent' => $parent, 'group' => null], ['position' => 'ASC']);
    }

    public function findMaxPosition(?Forum $parent, ?ForumGroup $group): ?int
    {
        $qb = $this->createQueryBuilder('f')
            ->select('MAX(f.position)');

        if ($parent !== null) {
            $qb->andWhere('f.parent = :parent')
                ->setParameter('parent', $parent);
        } else {
            $qb->andWhere('f.parent IS NULL');
        }

        if ($group !== null) {
            $qb->andWhere('f.group = :group')
                ->setParameter('group', $group);
        } else {
            $qb->andWhere('f.group IS NULL');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function save(object $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(object $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
