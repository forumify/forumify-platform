<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ApiTest extends ApiTestCase
{
    public function testUnauthorized(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/forums');
        self::assertResponseStatusCodeSame(401);
    }
}
