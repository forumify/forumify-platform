<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;

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
}
