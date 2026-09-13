<?php

declare(strict_types=1);

namespace Forumify\Testing\Api\Crud;

trait PostTestTrait
{
    use CrudApiTestRequirements;

    public function testPost(): void
    {
        $data = $this->getPostBody();
        if (array_is_list($data) && count($data) === 2) {
            [$data, $expected] = $data;
        } else {
            $expected = $data;
        }

        $response = $this->post(self::endpoint(), [
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
    abstract protected function getPostBody(): array;

    abstract protected function post(string $endpoint, array $options = []): array;
}
