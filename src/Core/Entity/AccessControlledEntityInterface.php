<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

interface AccessControlledEntityInterface
{
    /** @return array<string> */
    public function getACLPermissions(): array;

    public function getACLParameters(): ACLParameters;
}
