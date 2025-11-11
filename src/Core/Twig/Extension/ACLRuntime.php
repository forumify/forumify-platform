<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Service\ACLService;
use Twig\Extension\RuntimeExtensionInterface;

class ACLRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly ACLService $aclService)
    {
    }

    public function can(string $permission, AccessControlledEntityInterface $entity): bool
    {
        return $this->aclService->can($permission, $entity);
    }
}
