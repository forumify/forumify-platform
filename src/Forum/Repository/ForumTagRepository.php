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

    public function findByForum(?Forum $forum, ?bool $default = null): array
    {
        $forumTags = $forum === null ? [] : $this->findByForumRecursive($forum, $default);

        $criteria = ['forum' => null];
        if ($default !== null) {
            $criteria['default'] = $default;
        }
        $globalTags = $this->findBy($criteria);

        return array_merge($forumTags, $globalTags);
    }

    private function findByForumRecursive(Forum $forum, ?bool $default, bool $onlyInSubforums = false): array
    {
        $criteria = ['forum' => $forum];
        if ($onlyInSubforums) {
            $criteria['allowInSubforums'] = true;
        }
        if ($default !== null) {
            $criteria['default'] = $default;
        }

        $tags = $this->findBy($criteria);
        $parent = $forum->getParent();
        if ($parent = $forum->getParent()) {
            $tags = array_merge($tags, $this->findByForumRecursive($parent, $default, true));
        }

        return $tags;
    }
}
