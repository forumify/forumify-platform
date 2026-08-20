<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use ApiPlatform\Metadata\IriConverterInterface;
use Forumify\Testing\Api\ApiTestCase;
use Forumify\Testing\Api\Crud\DeleteTestTrait;
use Forumify\Testing\Api\Crud\GetCollectionTestTrait;
use Forumify\Testing\Api\Crud\GetTestTrait;
use Forumify\Testing\Api\Crud\PatchTestTrait;
use Forumify\Testing\Factories\Forum\ForumFactory;
use Forumify\Testing\Factories\Forum\ForumTagFactory;
use Forumify\Testing\Factories\Forum\TopicFactory;

class TopicApiTest extends ApiTestCase
{
    use GetTestTrait;
    use GetCollectionTestTrait;
    use PatchTestTrait;
    use DeleteTestTrait;

    protected static function factory(): string
    {
        return TopicFactory::class;
    }

    protected static function endpoint(): string
    {
        return '/api/forums/topics';
    }

    protected function getPatchBody(): array
    {
        return ['title' => 'Test Topic'];
    }

    public function testGetCollectionOnForum(): void
    {
        $topic1 = TopicFactory::createOne();
        $topic2 = TopicFactory::createOne();

        $response = $this->getCollection("/api/forums/{$topic1->getForum()->getId()}/topics");

        self::assertCount(1, $response);

        $allIds = array_column($response, 'id');
        self::assertContains($topic1->getId(), $allIds);
        self::assertNotContains($topic2->getId(), $allIds);
    }

    public function testPostOnForum(): void
    {
        $forum = ForumFactory::createOne();
        $forumIri = self::getContainer()->get(IriConverterInterface::class)->getIriFromResource($forum);

        $response = $this->post("/api/forums/{$forum->getId()}/topics", [
            'json' => ['title' => 'Test Topic'],
        ]);

        self::assertIsInt($response['id']);
        self::assertEquals($forumIri, $response['forum']);
    }

    public function testCanAssignAndGetTags(): void
    {
        $forum = ForumFactory::createOne();
        $tag = ForumTagFactory::createOne(['forum' => $forum]);
        $tagIri = "/api/forums/forum-tags/{$tag->getId()}";

        $topic = $this->post("/api/forums/{$forum->getId()}/topics", [
            'json' => [
                'title' => 'Topic With Tag',
                'tags' => [$tagIri],
            ],
        ]);
        self::assertArrayHasKey('tags', $topic);
        self::assertContains($tagIri, $topic['tags']);

        $topic = $this->get($topic['@id']);
        self::assertArrayHasKey('tags', $topic);
        self::assertContains($tagIri, $topic['tags']);
    }
}
