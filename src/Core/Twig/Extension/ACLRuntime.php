<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

class ACLRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function canAccess(string $permission, AccessControlledEntityInterface $entity, $default = false): bool
    {
        return $this->security->isGranted(VoterAttribute::ACL->value, [
            'permission' => $permission,
            'entity' => $entity,
            'default' => $default,
        ]);
    }
}
