<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Exception;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Forum;

/**
 * @extends AbstractRepository<Forum>
 */
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

    public function getHighestPosition(object $entity): int
    {
        $qb = $this->createQueryBuilder('f')
            ->select('MAX(f.position)');

        $parent = $entity->getParent();
        if ($parent !== null) {
            $qb->andWhere('f.parent = :parent')->setParameter('parent', $parent);
        } else {
            $qb->andWhere('f.parent IS NULL');
        }

        $group = $entity->getGroup();
        if ($group !== null) {
            $qb->andWhere('f.group = :group')->setParameter('group', $group);
        } else {
            $qb->andWhere('f.group IS NULL');
        }

        try {
            return (int)$qb->getQuery()->getSingleScalarResult();
        } catch (Exception) {
            return 0;
        }
    }
}
