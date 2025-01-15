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
        if ($user instanceof User) {
            $user->setLastActivity(new DateTime());
            $this->userRepository->save($user);
        }
    }
}

