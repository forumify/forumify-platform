<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Event\CommentCreatedEvent;
use Forumify\Forum\Form\NewComment;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateCommentService
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ReadMarkerRepository $readMarkerRepository,
    ) {
    }

    public function createComment(Topic $topic, NewComment $newComment): Comment
    {
        $comment = new Comment();
        $comment->setContent($newComment->getContent());
        $comment->setTopic($topic);
        $comment->setCreatedBy($newComment->getAuthor());
        $this->commentRepository->save($comment);
        $this->readMarkerRepository->unread(Topic::class, $topic->getId());

        $this->eventDispatcher->dispatch(new CommentCreatedEvent($comment));
        return $comment;
    }
}
