<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Crud;

trait GetCollectionTestTrait
{
    use CrudApiTestRequirements;

    public function testGetCollection(): void
    {
        $initialCount = self::factory()::count();
        self::factory()::createMany(30);
        $response = $this->get(self::endpoint(), ['query' => ['_limit' => 25]]);

        self::assertResponseIsSuccessful();
        self::assertArrayHasKey('member', $response);
        self::assertCount(25, $response['member']);
        self::assertEquals($initialCount + 30, $response['totalItems']);
    }

    abstract protected function get(string $endpoint, array $options = []): array;
}
