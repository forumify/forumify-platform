<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use Forumify\Testing\Api\ApiTestCase;
use Forumify\Testing\Api\Crud\DeleteTestTrait;
use Forumify\Testing\Api\Crud\GetCollectionTestTrait;
use Forumify\Testing\Api\Crud\GetTestTrait;
use Forumify\Testing\Api\Crud\PatchTestTrait;
use Forumify\Testing\Api\Crud\PostTestTrait;
use Forumify\Testing\Factories\Forum\ReactionFactory;

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
