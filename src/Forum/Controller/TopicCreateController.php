<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Form\NewTopicType;
use Forumify\Forum\Service\CreateTopicService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TopicCreateController extends AbstractController
{
    #[Route('/forum/{id}/topic/create', name: 'topic_create', requirements: ['forumId' => '\d+'])]
    public function __invoke(
        Forum $forum,
        Request $request,
        CreateTopicService $createTopicService,
    ): Response {
        $this->denyAccessUnlessGranted(VoterAttribute::ACL->value, [
            'permission' => 'create_topic',
            'entity' => $forum,
        ]);

        $form = $this->createForm(NewTopicType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $topic = $createTopicService->createTopic($forum, $form->getData());
            return $this->redirectToRoute('forumify_forum_topic', [
                'slug' => $topic->getSlug(),
            ]);
        }

        return $this->render('@Forumify/frontend/forum/topic_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
