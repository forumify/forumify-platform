<?php

declare(strict_types=1);

namespace Forumify\Admin\Service;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Service\LastCommentService;

class ForumDeleteService
{
    public function __construct(
        private readonly ForumRepository $forumRepository,
        private readonly ForumGroupDeleteService $forumGroupDeleteService,
        private readonly LastCommentService $lastCommentService
    ) {
    }

    public function deleteForum(Forum $forum, bool $clearCache = true): void
    {
        foreach ($forum->getGroups() as $group) {
            $this->forumGroupDeleteService->deleteForumGroup($group);
        }

        $this->deleteWithSubforums($forum);

        if ($clearCache) {
            $this->lastCommentService->clearCache();
        }
    }

    private function deleteWithSubforums(Forum $forum): void
    {
        foreach ($forum->getChildren() as $childForum) {
            $this->deleteForum($childForum, false);
        }

        $this->forumRepository->save($forum);
        $this->forumRepository->remove($forum);
    }
}
