<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Forumify\Core\Entity\User;
use Forumify\Forum\Repository\CommentRepository;
use Forumify\Forum\Repository\SubscriptionRepository;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Forumify\\ProfileHeader', '@Forumify/frontend/components/profile_header.html.twig')]
class ProfileHeader
{
    public User $user;
    public bool $showUserActions = true;

    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly TopicRepository $topicRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
    ) {
    }

    public function getTopicCount(): int
    {
        return $this->topicRepository->count(['createdBy' => $this->user]);
    }

    public function getCommentCount(): int
    {
        return $this->commentRepository->count(['createdBy' => $this->user]);
    }

    public function getFollowerCount(): int
    {
        return $this->subscriptionRepository->count([
            'type' => 'user_follow',
            'subjectId' => $this->user->getId(),
        ]);
    }

    public function getFollowingCount(): int
    {
        return $this->subscriptionRepository->count(['user' => $this->user, 'type' => 'user_follow']);
    }
}
