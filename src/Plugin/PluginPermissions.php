<?php

declare(strict_types=1);

namespace Forumify\Plugin;

class PluginPermissions
{
    private array $permissions;

    public function __construct(array $permissions)
    {
        $this->permissions = $this->convertPermissions($permissions);
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    private function convertPermissions(array $permissions): array
    {
        $convertedPermissions = [];

        foreach ($permissions as $plugin => $categories) {
            foreach ($categories as $category => $subcategories) {
                foreach ($subcategories as $subcategory => $perms) {
                    foreach ($perms as $permission) {
                        $convertedPermissions[] = "$plugin.$category.$subcategory.$permission";
                    }
                }
            }
        }

        return $convertedPermissions;
    }
}