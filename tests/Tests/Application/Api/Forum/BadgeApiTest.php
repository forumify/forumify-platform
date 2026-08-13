<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use Forumify\Testing\Api\ApiTestCase;
use Forumify\Testing\Api\Crud\DeleteTestTrait;
use Forumify\Testing\Api\Crud\GetCollectionTestTrait;
use Forumify\Testing\Api\Crud\GetTestTrait;
use Forumify\Testing\Api\Crud\PatchTestTrait;
use Forumify\Testing\Api\Crud\PostTestTrait;
use Forumify\Testing\Factories\Forum\BadgeFactory;

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
