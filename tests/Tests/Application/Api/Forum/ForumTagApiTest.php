<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use Tests\Tests\Application\Api\ApiTestCase;
use Tests\Tests\Application\Api\Crud\DeleteTestTrait;
use Tests\Tests\Application\Api\Crud\GetCollectionTestTrait;
use Tests\Tests\Application\Api\Crud\GetTestTrait;
use Tests\Tests\Application\Api\Crud\PatchTestTrait;
use Tests\Tests\Application\Api\Crud\PostTestTrait;
use Tests\Tests\Factories\Forum\ForumFactory;
use Tests\Tests\Factories\Forum\ForumTagFactory;

class ForumTagApiTest extends ApiTestCase
{
    use GetCollectionTestTrait;
    use GetTestTrait;
    use PostTestTrait;
    use PatchTestTrait;
    use DeleteTestTrait;

    protected static function factory(): string
    {
        return ForumTagFactory::class;
    }

    protected static function endpoint(): string
    {
        return '/api/forums/forum-tags';
    }

    protected function getPostBody(): array
    {
        return ['title' => 'Test Tag'];
    }

    protected function getPatchBody(): array
    {
        return ['title' => 'renamed'];
    }

    public function testGetAvailableTags(): void
    {
        $parentForum = ForumFactory::createOne();
        $forum = ForumFactory::createOne(['parent' => $parentForum]);

        $globalTag = ForumTagFactory::createOne([
            'forum' => null,
            'allowInSubforums' => true,
        ]);
        $parentTag = ForumTagFactory::createOne([
            'forum' => $parentForum,
            'allowInSubforums' => true,
        ]);
        $parentTagDisallowed = ForumTagFactory::createOne([
            'forum' => $parentForum,
            'allowInSubforums' => false,
        ]);
        $directTag = ForumTagFactory::createOne([
            'forum' => $forum,
        ]);

        $response = $this->getCollection("/api/forums/{$forum->getId()}/available-tags");
        $ids = array_column($response, 'id');

        self::assertContains($globalTag->getId(), $ids);
        self::assertContains($parentTag->getId(), $ids);
        self::assertNotContains($parentTagDisallowed->getId(), $ids);
        self::assertContains($directTag->getId(), $ids);
    }
}
