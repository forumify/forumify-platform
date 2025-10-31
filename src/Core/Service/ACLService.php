<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Bundle\SecurityBundle\Security;

class ACLService
{
    public function __construct(private readonly Security $security)
    {
    }

    public function can(string $permission, AccessControlledEntityInterface $entity): bool
    {
        return $this->security->isGranted(VoterAttribute::ACL->value, [
            'permission' => $permission,
            'entity' => $entity,
        ]);
    }
}
