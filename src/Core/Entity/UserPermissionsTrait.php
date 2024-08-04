<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\Common\Collections\Collection;

trait UserPermissionsTrait
{
    private ?array $permissions = null;

    /**
     * @return Collection<Role>
     */
    abstract private function getRoleEntities(): Collection;

    public function getPermissions(): array
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }

        $this->permissions = [];
        foreach ($this->getRoleEntities() as $role) {
            foreach ($role->getPermissions() as $permission) {
                $this->permissions[] = $permission;
            }
        }
        return $this->permissions;
    }
}
