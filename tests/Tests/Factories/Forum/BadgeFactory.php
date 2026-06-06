<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\Forum;

use Forumify\Forum\Entity\Badge;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Badge>
 */
class BadgeFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Badge::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->word(),
            'description' => self::faker()->sentence(),
            'image' => 'badge.webp',
        ];
    }
}
