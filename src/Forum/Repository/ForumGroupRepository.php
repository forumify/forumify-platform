<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Exception;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;

/**
 * @extends AbstractRepository<ForumGroup>
 */
class ForumGroupRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return ForumGroup::class;
    }

    /**
     * @return array<ForumGroup>
     */
    public function findByParent(?Forum $parent): array
    {
        return $this->findBy(['parentForum' => $parent], ['position' => 'ASC']);
    }

    public function getHighestPosition(object $entity): int
    {
        $qb = $this->createQueryBuilder('fg')
            ->select('MAX(fg.position)');

        $parent = $entity->getParentForum();
        if ($parent !== null) {
            $qb->where('fg.parentForum = :parent')
                ->setParameter('parent', $parent);
        } else {
            $qb->where('fg.parentForum IS NULL');
        }

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (Exception) {
            return 0;
        }
    }
}
