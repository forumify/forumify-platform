<?php

declare(strict_types=1);

namespace Forumify\Testing\Api\Crud;

trait GetTestTrait
{
    use CrudApiTestRequirements;

    public function testGet(): void
    {
        $entity = self::factory()::createOne();

        $response = $this->get(self::endpoint() . '/' . $this->getIdentifier($entity));

        self::assertResponseIsSuccessful();
        self::assertNotEmpty($response);
    }

    abstract protected function get(string $endpoint, array $options = []): array;
}
