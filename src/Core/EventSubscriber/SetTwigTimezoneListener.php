<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Forumify\Core\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Extension\CoreExtension;

#[AsEventListener(KernelEvents::REQUEST)]
class SetTwigTimezoneListener
{
    public function __construct(
        private readonly Security $security,
        private readonly Environment $twig
    ) {
    }

    public function __invoke(): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $coreExt = $this->twig->getExtension(CoreExtension::class);
        $coreExt->setTimezone($user->getTimezone());
    }
}
