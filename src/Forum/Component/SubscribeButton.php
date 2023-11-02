<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Subscription;
use Forumify\Forum\Repository\SubscriptionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: '@Forumify/components/subscribe_button.html.twig')]
class SubscribeButton
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $entityId;

    #[LiveProp]
    public string $subscriptionType;

    #[LiveProp]
    public string $subscribeIcon = 'ph ph-bell';

    #[LiveProp]
    public string $subscribeLabel = 'subscribe';

    #[LiveProp]
    public string $unsubscribeIcon = 'ph ph-bell-slash';

    #[LiveProp]
    public string $unsubscribeLabel = 'unsubscribe';

    #[LiveProp]
    public string $buttonClass = 'btn-outlined';

    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly Security $security
    ) {
    }

    public function getSubscription(): ?Subscription
    {
        $user = $this->getUser();
        if ($user === null) {
            return null;
        }

        return $this->subscriptionRepository->findOneBy([
            'user' => $user,
            'type' => $this->subscriptionType,
            'subjectId' => $this->entityId,
        ]);
    }

    #[LiveAction]
    public function toggleSubscription(): void
    {
        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedException('You must be logged in to subscribe.');
        }

        $subscription = $this->getSubscription();
        if ($subscription !== null) {
            $this->subscriptionRepository->remove($subscription);
            return;
        }

        $subscription = new Subscription();
        $subscription->setUser($this->getUser());
        $subscription->setType($this->subscriptionType);
        $subscription->setSubjectId($this->entityId);

        $this->subscriptionRepository->save($subscription);
    }

    public function getUser(): ?User
    {
        /** @var User $user */
        $user = $this->security->getUser();
        return $user;
    }
}
