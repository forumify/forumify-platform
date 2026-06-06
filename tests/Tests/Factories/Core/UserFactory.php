<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Core;

use Forumify\Core\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
class UserFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'username' => self::faker()->userName(),
            'email' => self::faker()->email(),
            'emailVerified' => true,
            'banned' => false,
        ];
    }
}
