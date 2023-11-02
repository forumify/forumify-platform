<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Form\CommentType;
use Forumify\Forum\Service\CreateCommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TopicController extends AbstractController
{
    #[Route('/topic/{slug}', name: 'topic')]
    public function __invoke(Topic $topic, Request $request, CreateCommentService $commentService): Response
    {
        $form = $this->createForm(CommentType::class, options: [
            'label' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commentService->createComment($topic, $form->getData());
            return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
        }

        return $this->render('@Forumify/frontend/forum/topic.html.twig', [
            'topic' => $topic,
            'form' => $form->createView(),
        ]);
    }
}
