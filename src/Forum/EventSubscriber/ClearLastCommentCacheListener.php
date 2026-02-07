<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Service\LastCommentService;

#[AsEntityListener(event: Events::postPersist, method: 'clearLastCommentCache', entity: Comment::class)]
#[AsEntityListener(event: Events::postRemove, method: 'clearLastCommentCache', entity: Comment::class)]
class ClearLastCommentCacheListener
{
    public function __construct(private readonly LastCommentService $lastCommentService)
    {
    }

    public function clearLastCommentCache(): void
    {
        $this->lastCommentService->clearCache();
    }
}
