<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Service\LastCommentService;

#[AsEntityListener(event: Events::postPersist, method: 'collectComment', entity: Comment::class)]
#[AsEntityListener(event: Events::postRemove, method: 'collectComment', entity: Comment::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'collectTopic', entity: Topic::class)]
#[AsDoctrineListener(event: Events::postFlush)]
class ClearLastCommentCacheListener
{
    /** @var array<int, true> */
    private array $changedForumIds = [];

    public function __construct(private readonly LastCommentService $lastCommentService)
    {
    }

    public function collectComment(Comment $comment): void
    {
        $this->changedForumIds[$comment->getTopic()->getForum()->getId()] = true;
    }

    public function collectTopic(Topic $topic, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('forum')) {
            foreach ([$args->getOldValue('forum'), $args->getNewValue('forum')] as $forum) {
                if ($forum instanceof Forum) {
                    $this->changedForumIds[$forum->getId()] = true;
                }
            }
        }

        if ($args->hasChangedField('hidden')) {
            $this->changedForumIds[$topic->getForum()->getId()] = true;
        }
    }

    public function postFlush(): void
    {
        $changedForumIds = array_keys($this->changedForumIds);
        $this->changedForumIds = [];

        foreach ($changedForumIds as $id) {
            $this->lastCommentService->clearCacheForForum($id);
        }
    }
}
