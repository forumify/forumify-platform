<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Forumify\Forum\Event\CommentCreatedEvent;
use Forumify\Forum\Event\CommentDeletedEvent;
use Forumify\Forum\Service\LastCommentService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearLastCommentServiceSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly LastCommentService $lastCommentService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'clearLastCommentCache',
            CommentDeletedEvent::class => 'clearLastCommentCache',
        ];
    }

    public function clearLastCommentCache(): void
    {
        $this->lastCommentService->clearCache();
    }
}
