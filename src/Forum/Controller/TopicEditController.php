<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TopicEditController extends AbstractController
{
    #[Route('/topic/{slug}/edit', 'topic_edit')]
    public function __invoke(Request $request, Topic $topic, TopicRepository $topicRepository): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::TopicEdit->value, $topic);

        $formBuilder = $this->createFormBuilder($topic, ['data_class' => Topic::class]);
        $formBuilder->add('title', TextType::class);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $topic = $form->getData();
            $topicRepository->save($topic);

            $this->addFlash('success', 'flashes.topic_saved');
            return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
        }

        return $this->render('@Forumify/frontend/forum/topic_edit.html.twig', [
            'topic' => $topic,
            'form' => $form->createView(),
        ]);
    }
}
