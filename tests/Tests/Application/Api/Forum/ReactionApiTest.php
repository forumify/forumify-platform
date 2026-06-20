<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use Tests\Tests\Application\Api\ApiTestCase;
use Tests\Tests\Application\Api\Crud\DeleteTestTrait;
use Tests\Tests\Application\Api\Crud\GetCollectionTestTrait;
use Tests\Tests\Application\Api\Crud\GetTestTrait;
use Tests\Tests\Application\Api\Crud\PatchTestTrait;
use Tests\Tests\Application\Api\Crud\PostTestTrait;
use Tests\Tests\Factories\Forum\ReactionFactory;

class ReactionApiTest extends ApiTestCase
{
    use GetCollectionTestTrait;
    use GetTestTrait;
    use PostTestTrait;
    use PatchTestTrait;
    use DeleteTestTrait;

    protected static function factory(): string
    {
        return ReactionFactory::class;
    }

    protected static function endpoint(): string
    {
        return '/api/forums/reactions';
    }

    protected function getPostBody(): array
    {
        return [
            [
                'name' => 'Test Reaction',
                'newImage' => [
                    'filename' => 'reaction.png',
                    'data' => base64_encode(file_get_contents(TEST_DATA_DIR . '/forumify.png')),
                ],
                'reputation' => 0,
            ],
            function (array $data) {
                self::assertEquals('Test Reaction', $data['name']);
                self::assertStringStartsWith('/storage/assets/', $data['image']);
                self::assertStringEndsWith('reaction.png', $data['image']);
            },
        ];
    }

    protected function getPatchBody(): array
    {
        return ['name' => 'Blip Reaction'];
    }
}
