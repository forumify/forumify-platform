<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TopicDeleteController extends AbstractController
{
    #[Route('/topic/{slug}/delete', 'topic_delete')]
    public function __invoke(Topic $topic, TopicRepository $topicRepository): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::TopicDelete->value, $topic);

        $parentSlug = $topic->getParent()->getSlug();
        $topicRepository->remove($topic);

        $this->addFlash('success', 'flashes.topic_removed');
        return $this->redirectToRoute('forumify_forum_forum', ['slug' => $parentSlug]);
    }
}
