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
    /**
     * @param iterable<class-string, ReadMarkerServiceInterface<object>> $readMarkerServices
     */
    public function __construct(
        #[AutowireIterator('forumify.read_marker.service', defaultIndexMethod: 'getEntityClass')]
        private readonly iterable $readMarkerServices,
        private readonly Security $security,
    ) {
    }

    public function read(mixed $subject, ?User $user = null): bool
    {
        $user ??= $this->security->getUser();
        if (!$user instanceof User) {
            return true;
        }

        foreach ($this->readMarkerServices as $class => $checker) {
            if (is_a($subject, $class)) {
                return $checker->read($user, $subject);
            }
        }
        throw new RuntimeException('No read marker service exists for subject ' . get_class($subject));
    }
}
