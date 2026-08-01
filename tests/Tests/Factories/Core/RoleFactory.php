<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Core;

use Forumify\Core\Entity\Role;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Role>
 */
class RoleFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Role::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->jobTitle(),
        ];
    }
}
