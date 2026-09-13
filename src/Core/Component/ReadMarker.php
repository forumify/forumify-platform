<?php

declare(strict_types=1);

namespace Forumify\Core\Component;

use Forumify\Core\Entity\User;
use Forumify\Core\Service\ReadMarkerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('ReadMarker', '@Forumify/components/read_marker.html.twig')]
class ReadMarker
{
    public object $item;

    public function __construct(
        private readonly ReadMarkerRegistry $registry,
        private readonly Security $security,
    ) {
    }

    public function getType(): string
    {
        return $this->registry->getType($this->item);
    }

    public function getId(): int
    {
        return $this->registry->getId($this->item);
    }

    public function isRead(): bool
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return true;
        }

        return $this->registry->getServiceForSubject($this->item)->read($user, $this->item);
    }
}
