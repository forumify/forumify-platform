<?php

declare(strict_types=1);

namespace Tests\Tests\Factories\OAuth;

use Forumify\OAuth\Entity\OAuthClient;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<OAuthClient>
 */
class OAuthClientFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return OAuthClient::class;
    }

    protected function defaults(): array|callable
    {
        return [];
    }
}
