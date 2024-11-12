<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MarkAsAnswerController extends AbstractController
{
    #[Route('/comment/{id}/mark-as-answer', 'comment_answer')]
    #[IsGranted(VoterAttribute::CommentMarkAsAnswer->value, new Expression('args["comment"]'))]
    public function __invoke(
        Comment $comment,
        TopicRepository $topicRepository,
    ): Response {
        $topic = $comment->getTopic();
        $response = $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);

        if ($topic->getForum()->getType() !== Forum::TYPE_SUPPORT) {
            return $response;
        }

        $topic->setAnswer($comment);
        $topicRepository->save($topic);

        $this->addFlash('success', 'forum.comment.marked_as_answer');
        return $response;
    }
}
