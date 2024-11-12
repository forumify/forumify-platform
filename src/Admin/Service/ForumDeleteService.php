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
        private readonly LastCommentService $lastCommentService
    ) {
    }

    public function deleteForum(Forum $forum): void
    {
        $this->deleteWithSubforums($forum);
        $this->lastCommentService->clearCache();
    }

    private function deleteWithSubforums(Forum $forum): void
    {
        foreach ($forum->getChildren() as $childForum) {
            $this->deleteForum($childForum);
        }

        $this->forumRepository->save($forum);
        $this->forumRepository->remove($forum);
    }
}
