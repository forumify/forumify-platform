<?php

declare(strict_types=1);

namespace Forumify\Admin\Service;

use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Repository\ForumGroupRepository;
use Forumify\Forum\Repository\ForumRepository;

class ForumGroupDeleteService
{
    public function __construct(
        private readonly ForumGroupRepository $forumGroupRepository,
        private readonly ForumRepository $forumRepository,
    ) {
    }

    public function deleteForumGroup(ForumGroup $group): void
    {
        $this->ungroupForums($group);
        $this->forumGroupRepository->remove($group);
    }

    public function ungroupForums(ForumGroup $group): void
    {
        $position = $this->forumRepository->getHighestPosition($group->getParentForum(), null);
        foreach ($group->getForums() as $forum) {
            $forum->setPosition(++$position);
            $forum->setGroup(null);
            $this->forumRepository->save($forum, false);
        }

        $this->forumRepository->flush();
    }
}
