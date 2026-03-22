<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Forum;

use Forumify\Forum\Entity\Reaction;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

class ReactionFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Reaction::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->word(),
            'image' => self::faker()->url(),
            'reputation' => self::faker()->numberBetween(1, 100),
        ];
    }
}
