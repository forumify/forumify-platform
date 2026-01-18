<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumTag;

/**
 * @extends AbstractRepository<ForumTag>
 */
class ForumTagRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return ForumTag::class;
    }

    /**
     * TODO: cache this
     */
    public function findByForum(?Forum $forum): array
    {
        $forumTags = $forum === null ? [] : $this->findByForumRecursive($forum);
        $globalTags = $this->findBy(['forum' => null]);

        return array_merge($forumTags, $globalTags);
    }

    private function findByForumRecursive(Forum $forum, $onlyInSubforums = false): array
    {
        $criteria = ['forum' => $forum];
        if ($onlyInSubforums) {
            $criteria['allowInSubforums'] = true;
        }

        $tags = $this->findBy($criteria);
        $parent = $forum->getParent();
        if ($parent = $forum->getParent()) {
            $tags = array_merge($tags, $this->findByForumRecursive($parent, true));
        }

        return $tags;
    }
}
