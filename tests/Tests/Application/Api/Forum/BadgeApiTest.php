<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use Tests\Tests\Application\Api\ApiTestCase;
use Tests\Tests\Application\Api\Crud\DeleteTestTrait;
use Tests\Tests\Application\Api\Crud\GetCollectionTestTrait;
use Tests\Tests\Application\Api\Crud\GetTestTrait;
use Tests\Tests\Application\Api\Crud\PatchTestTrait;
use Tests\Tests\Application\Api\Crud\PostTestTrait;
use Tests\Tests\Factories\Forum\BadgeFactory;

class BadgeApiTest extends ApiTestCase
{
    use GetCollectionTestTrait;
    use GetTestTrait;
    use PostTestTrait;
    use PatchTestTrait;
    use DeleteTestTrait;

    protected static function factory(): string
    {
        return BadgeFactory::class;
    }

    protected static function endpoint(): string
    {
        return '/api/forums/badges';
    }

    protected function getPostBody(): array
    {
        return [
            [
                'name' => 'Test Badge',
                'description' => 'Badge for testing purposes',
                'newImage' => [
                    'filename' => 'badge.png',
                    'data' => base64_encode(file_get_contents(TEST_DATA_DIR . '/forumify.png')),
                ],
            ],
            function (array $data) {
                self::assertEquals('Test Badge', $data['name']);
                self::assertStringStartsWith('/storage/assets/', $data['image']);
                self::assertStringEndsWith('badge.png', $data['image']);
            },
        ];
    }

    protected function getPatchBody(): array
    {
        return ['name' => 'Blip Badge'];
    }
}
