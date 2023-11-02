<?php

declare(strict_types=1);

namespace Forumify\Admin\Service;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;

class ForumDeleteService
{
    public function __construct(
        private readonly ForumRepository $forumRepository,
    ) {
    }

    public function deleteForum(Forum $forum): void
    {
        foreach ($forum->getChildren() as $childForum) {
            $this->deleteForum($childForum);
        }

        $forum->setLastComment(null);
        $this->forumRepository->save($forum);
        $this->forumRepository->remove($forum);
    }
}
