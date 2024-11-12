<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Event\CommentDeletedEvent;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommentDeleteController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route('/comment/{id}/delete', 'comment_delete')]
    public function __invoke(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::CommentDelete->value, $comment);

        $topic = $comment->getTopic();
        $this->commentRepository->remove($comment);
        $this->eventDispatcher->dispatch(new CommentDeletedEvent($comment));

        if ($topic->getComments()->isEmpty()) {
            return $this->redirectToRoute('forumify_forum_topic_delete', ['slug' => $topic->getSlug()]);
        }

        $this->addFlash('success', 'flashes.comment_removed');
        return $this->redirectToRoute('forumify_forum_topic', ['slug' => $comment->getTopic()->getSlug()]);
    }
}
