<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Entity\User;
use Forumify\Forum\Repository\CommentRepository;
use Forumify\Forum\Repository\SubscriptionRepository;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly TopicRepository $topicRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
    ) {
    }

    #[Route('/profile/{username}', name: 'profile')]
    public function __invoke(User $user): Response
    {
        $followerCount = $this->subscriptionRepository->count(['type' => 'user_follow', 'subjectId' => $user->getId()]);
        $followingCount = $this->subscriptionRepository->count(['user' => $user, 'type' => 'user_follow']);

        return $this->render('@Forumify/frontend/profile.html.twig', [
            'user' => $user,
            'topicCount' => $this->topicRepository->count(['createdBy' => $user]),
            'commentCount' => $this->commentRepository->count(['createdBy' => $user]),
            'comments' => $this->commentRepository->findBy(['createdBy' => $user], ['createdAt' => 'DESC'], 10),
            'followerCount' => $followerCount,
            'followingCount' => $followingCount,
        ]);
    }

    #[Route('/profile/{id}', 'profile_id', requirements: ['id' => '\d+'], priority: 1)]
    public function profileById(User $user): Response
    {
        return $this->redirectToRoute('forumify_forum_profile', [
            'username' => $user->getUsername()
        ], Response::HTTP_MOVED_PERMANENTLY);
    }
}
