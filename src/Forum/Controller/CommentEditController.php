<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\MarkdownParser;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentEditController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly MarkdownParser $parser
    ) {
    }

    #[Route('/comment/{id}/edit', 'comment_edit', methods: ['POST'])]
    public function __invoke(Request $request, Comment $comment): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::CommentEdit->value, $comment);

        $comment->setContent($request->getContent());
        $this->commentRepository->save($comment);

         return new Response($this->parser->parse($comment->getContent()));
    }
}
