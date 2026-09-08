<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Service\QuoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentQuoteController extends AbstractController
{
    public function __construct(private readonly QuoteService $quoteService)
    {
    }

    #[Route('/comment/{id}/quote', 'comment_quote', methods: ['GET'])]
    public function __invoke(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::CommentView->value, $comment);

        $topic = $comment->getTopic();
        $source = $this->generateUrl('forumify_forum_topic', [
            'slug' => $topic->getSlug(),
            'comment' => $comment->getId(),
        ]);

        return new Response($this->quoteService->createQuote(
            $comment->getContent(),
            $comment->getCreatedBy(),
            $comment->getCreatedAt(),
            $source,
        ));
    }
}
