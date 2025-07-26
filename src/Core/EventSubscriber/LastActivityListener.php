<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use DateTime;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(KernelEvents::REQUEST)]
class LastActivityListener
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $now = new DateTime();
        $lastActivity = $user->getLastActivity();
        if ($lastActivity === null || $now->getTimestamp() - $lastActivity->getTimestamp() >= 60) {
            $user->setLastActivity($now);
            $this->userRepository->save($user);
        }
    }
}
