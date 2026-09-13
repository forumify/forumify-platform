<?php

declare(strict_types=1);

namespace Forumify\Testing\Factories\Forum;

use Forumify\Forum\Entity\Reaction;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Reaction>
 */
class ReactionFactory extends PersistentObjectFactory
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
            'reputation' => self::faker()->randomElement([-1, 0, 1]),
        ];
    }
}
