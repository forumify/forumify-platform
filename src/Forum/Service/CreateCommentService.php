<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Service\SimpleRateLimiter;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Form\NewComment;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class CreateCommentService
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly SimpleRateLimiter $rateLimiter,
        RateLimiterFactoryInterface $forumifyCommentLimiter,
    ) {
        $rateLimiter->setLimiter($forumifyCommentLimiter);
    }

    public function createComment(Topic $topic, NewComment $newComment): Comment
    {
        $this->rateLimiter->limit();

        $comment = new Comment();
        $comment->setContent($newComment->getContent());
        $comment->setTopic($topic);
        $comment->setCreatedBy($newComment->getAuthor());
        $this->commentRepository->save($comment, false);

        return $comment;
    }
}
