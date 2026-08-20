<?php

declare(strict_types=1);

namespace Forumify\Testing\Factories\Core;

use Forumify\Core\Entity\ACL;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ACL>
 */
class ACLFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ACL::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'permission' => 'view',
            'roles' => [RoleFactory::findOrCreate(['slug' => 'user'])],
        ];
    }
}
