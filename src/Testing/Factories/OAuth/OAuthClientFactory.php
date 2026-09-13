<?php

declare(strict_types=1);

namespace Forumify\Testing\Factories\OAuth;

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
        return [
            'clientId' => self::faker()->userName(),
            'clientSecret' => self::faker()->password(12),
        ];
    }
}
