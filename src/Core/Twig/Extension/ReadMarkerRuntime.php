<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Entity\User;
use Forumify\Core\Service\ReadMarkerServiceInterface;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Twig\Extension\RuntimeExtensionInterface;

class ReadMarkerRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        /** @var iterable<ReadMarkerServiceInterface> */
        #[AutowireIterator('forumify.read_marker.service')]
        private readonly iterable $readMarkerCheckers,
        private readonly Security $security,
    ) {
    }

    public function read(mixed $subject, ?User $user = null): bool
    {
        if ($user === null) {
            /** @var User|null $loggedInUser */
            $loggedInUser = $this->security->getUser();
            $user = $loggedInUser;
        }

        if (!$user instanceof User) {
            return true;
        }

        foreach ($this->readMarkerCheckers as $checker) {
            if ($checker->supports($subject)) {
                return $checker->read($user, $subject);
            }
        }
        throw new RuntimeException('No read marker service exists for subject ' . get_class($subject));
    }
}
