<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use DateTime;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LastSeenSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'setLastSeen'];
    }

    public function setLastSeen(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $user->setLastLogin(new DateTime());
        $this->userRepository->save($user);
    }
}
