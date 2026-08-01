<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Crud;

trait DeleteTestTrait
{
    use CrudApiTestRequirements;

    public function testDelete(): void
    {
        $count = self::factory()::count();
        $entity = self::factory()::createOne();
        self::assertEquals($count + 1, self::factory()::count());

        $this->delete(self::endpoint() . '/' . $this->getIdentifier($entity));

        self::assertResponseIsSuccessful();
        self::assertEquals($count, self::factory()::count());
    }

    abstract protected function delete(string $endpoint, array $options = []): void;
}
