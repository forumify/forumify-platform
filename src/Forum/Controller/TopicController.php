<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Form\CommentType;
use Forumify\Forum\Service\CreateCommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TopicController extends AbstractController
{
    #[Route('/topic/{slug}', name: 'topic')]
    public function __invoke(
        Topic $topic,
        Request $request,
        CreateCommentService $commentService,
        Security $security,
    ): Response {
        $canComment = $security->isGranted(VoterAttribute::ACL->value, [
            'permission' => 'create_comment',
            'entity' => $topic->getForum(),
        ]);

        $commentForm = null;
        if ($canComment) {
            $commentForm = $this->createForm(CommentType::class, options: [
                'label' => false,
            ]);

            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $commentService->createComment($topic, $commentForm->getData());
                return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
            }
        }

        return $this->render('@Forumify/frontend/forum/topic.html.twig', [
            'topic' => $topic,
            'commentForm' => $commentForm?->createView(),
        ]);
    }
}
