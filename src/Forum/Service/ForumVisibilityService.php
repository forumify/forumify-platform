<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Service\ACLService;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\TopicVisibility;

/**
 * Resolves which topics the current user can see, per forum.
 */
class ForumVisibilityService
{
    public function __construct(
        private readonly ACLService $aclService,
        private readonly ForumTreeService $forumTree,
    ) {
    }

    /**
     * @return array<int, TopicVisibility> keyed by forum id, only containing viewable forums
     */
    public function getTopicVisibilityPerForum(): array
    {
        $visibility = [];
        foreach ($this->forumTree->getForums() as $id => $forum) {
            if ($this->aclService->can('view', $forum)) {
                $visibility[$id] = $this->getTopicVisibility($forum);
            }
        }

        return $visibility;
    }

    /**
     * @return array<int, TopicVisibility> keyed by forum id, for the forum and its viewable sub forums
     */
    public function getTopicVisibilityInTree(Forum $forum): array
    {
        $this->forumTree->getForums();

        $visibility = [$forum->getId() => $this->getTopicVisibility($forum)];
        foreach ($forum->getChildren() as $child) {
            if ($this->aclService->can('view', $child)) {
                $visibility += $this->getTopicVisibilityInTree($child);
            }
        }

        return $visibility;
    }

    public function getTopicVisibility(Forum $forum): TopicVisibility
    {
        $canViewAll = !$forum->getDisplaySettings()->isOnlyShowOwnTopics()
            || $this->aclService->can('show_all_topics', $forum);

        return TopicVisibility::forPermissions($canViewAll, $this->aclService->can('moderate', $forum));
    }
}
