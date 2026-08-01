<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Crud;

trait PatchTestTrait
{
    use CrudApiTestRequirements;

    public function testPatch(): void
    {
        $entity = self::factory()::createOne();
        $data = $this->getPatchBody();
        if (array_is_list($data) && count($data) === 2) {
            [$data, $expected] = $data;
        } else {
            $expected = $data;
        }

        $response = $this->patch(self::endpoint() . '/' . $this->getIdentifier($entity), [
            'json' => $data,
        ]);

        self::assertResponseIsSuccessful();
        if (is_callable($expected)) {
            $expected($response);
        } else {
            self::assertArraySubset($expected, $response);
        }
    }

    /**
     * @return array<string, mixed>|list<array<string, mixed>>|array{array<string, mixed>, callable}
     */
    abstract protected function getPatchBody(): array;

    abstract protected function patch(string $endpoint, array $options = []): array;
}
