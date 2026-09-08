<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Entity\User;
use Forumify\Core\Service\ReadMarkerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

class ReadMarkerRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ReadMarkerRegistry $registry,
        private readonly Security $security,
    ) {
    }

    public function read(mixed $subject, ?User $user = null): bool
    {
        $user ??= $this->security->getUser();
        if (!$user instanceof User) {
            return true;
        }

        return $this->registry->getServiceForSubject($subject)->read($user, $subject);
    }
}
