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

    public function getHighestPosition(?Forum $parent): int
    {
        $qb = $this->createQueryBuilder('fg')
            ->select('MAX(fg.position)')
            ->where('fg.parentForum IS NULL');

        if ($parent !== null) {
            $qb->where('fg.parentForum = :parent')
                ->setParameter('parent', $parent);
        }

        try {
            return $qb->getQuery()->getSingleScalarResult() ?? 0;
        } catch (Exception) {
            return 0;
        }
    }
}
