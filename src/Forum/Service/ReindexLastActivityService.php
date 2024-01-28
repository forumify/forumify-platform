<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\CommentRepository;
use Forumify\Forum\Repository\ForumRepository;

class ReindexLastActivityService
{
    public function __construct(
        private readonly ForumRepository $forumRepository,
        private readonly CommentRepository $commentRepository,
    ) {
    }

    /**
     * TODO: perf: currently it's only possible to reindex the entire tree,
     *  while 9/10 cases, only 1 branch needs to be re-indexed. For small forums this won't be an
     *  issue, but once we have large customers, it might be beneficial to unblock the message queue
     */
    public function reindexAll(): void
    {
        $rootForums = $this->forumRepository->findBy(['parent' => null]);

        /** @var Forum $rootForum */
        foreach ($rootForums as $rootForum) {
            $this->reindexChildren($rootForum);
        }

        $this->forumRepository->saveAll($rootForums);
    }

    private function reindexChildren(Forum $forum): void
    {
        $lastForumComment = $this->reindexTopics($forum);
        foreach ($forum->getChildren() as $childForum) {
            $this->reindexChildren($childForum);

            $lastChildForumComment = $this->reindexTopics($childForum);
            $childForum->setLastComment($lastChildForumComment);
            if ($lastChildForumComment === null) {
                continue;
            }

            if ($lastForumComment === null) {
                $lastForumComment = $lastChildForumComment;
            }

            if ($lastForumComment->getCreatedAt() < $lastChildForumComment->getCreatedAt()) {
                $lastForumComment = $lastChildForumComment;
            }
        }

        $forum->setLastComment($lastForumComment);
    }

    private function reindexTopics(Forum $forum): ?Comment
    {
        $lastForumComment = null;
        foreach ($forum->getTopics() as $topic) {
            $lastComment = $this->commentRepository->findLastCommentInTopic($topic);
            if ($lastComment === null) {
                continue;
            }

            $topic->setLastComment($lastComment);

            if ($lastForumComment === null || $lastForumComment->getCreatedAt() < $lastComment->getCreatedAt()) {
                $lastForumComment = $lastComment;
            }
        }
        return $lastForumComment;
    }
}
