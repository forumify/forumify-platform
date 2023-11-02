<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Subscription;
use Forumify\Forum\Repository\SubscriptionRepository;

class SubscriptionService
{
    public function __construct(private readonly SubscriptionRepository $subscriptionRepository)
    {
    }

    public function subscribe(User $user, string $type, int $subjectId): void
    {
        $existingSubscription = $this->subscriptionRepository->findOneBy([
            'user' => $user,
            'type' => $type,
            'subjectId' => $subjectId,
        ]);

        if ($existingSubscription !== null) {
            // user is already subscribed
            return;
        }

        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setType($type);
        $subscription->setSubjectId($subjectId);

        $this->subscriptionRepository->save($subscription);
    }
}
