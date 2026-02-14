<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Subscription;
use Forumify\Forum\Repository\SubscriptionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'SubscribeButton', template: '@Forumify/frontend/components/subscribe_button.html.twig')]
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
    public string $buttonClass = 'btn-outlined btn-icon';

    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly Security $security
    ) {
    }

    public function getSubscription(): ?Subscription
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
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
    #[IsGranted('ROLE_USER')]
    public function toggleSubscription(): void
    {
        $subscription = $this->getSubscription();
        if ($subscription !== null) {
            $this->subscriptionRepository->remove($subscription);
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setType($this->subscriptionType);
        $subscription->setSubjectId($this->entityId);

        $this->subscriptionRepository->save($subscription);
    }
}
