<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Service\LastCommentService;

#[AsEntityListener(event: Events::postPersist, method: 'clearCommentCache', entity: Comment::class)]
#[AsEntityListener(event: Events::postRemove, method: 'clearCommentCache', entity: Comment::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'clearTopicCache', entity: Topic::class)]
class ClearLastCommentCacheListener
{
    public function __construct(private readonly LastCommentService $lastCommentService)
    {
    }

    public function clearCommentCache(Comment $comment): void
    {
        $this->lastCommentService->clearCacheForForum($comment->getTopic()->getForum()->getId());
    }

    public function clearTopicCache(Topic $topic, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('hidden')) {
            $this->lastCommentService->clearCacheForForum($topic->getForum()->getId());
        }

        if (!$args->hasChangedField('forum')) {
            return;
        }

        foreach ([$args->getOldValue('forum'), $args->getNewValue('forum')] as $forum) {
            if ($forum instanceof Forum) {
                $this->lastCommentService->clearCacheForForum($forum->getId());
            }
        }
    }
}
