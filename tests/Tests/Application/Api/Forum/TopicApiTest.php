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

    public function testPostWithImagesReturnsUrls(): void
    {
        $forum = ForumFactory::createOne();

        $topic = $this->post("/api/forums/{$forum->getId()}/topics", [
            'json' => [
                'title' => 'Topic With Images',
                'newImages' => [
                    ['filename' => 'first.png', 'data' => self::imageData()],
                    ['filename' => 'second.png', 'data' => self::imageData()],
                ],
            ],
        ]);

        self::assertCount(2, $topic['images']);
        self::assertStringStartsWith('/storage/media/', $topic['images'][0]);
        self::assertStringEndsWith('first.png', $topic['images'][0]);
        self::assertStringEndsWith('second.png', $topic['images'][1]);

        self::assertSame($topic['images'], $this->get($topic['@id'])['images']);
    }

    public function testPatchReplacesImages(): void
    {
        $topic = TopicFactory::createOne(['images' => ['existing.png']]);

        $patched = $this->patch(self::endpoint() . "/{$topic->getId()}", [
            'json' => [
                'newImages' => [['filename' => 'replacement.png', 'data' => self::imageData()]],
            ],
        ]);

        self::assertCount(1, $patched['images']);
        self::assertStringEndsWith('replacement.png', $patched['images'][0]);
    }

    public function testPatchWithoutImagesLeavesThemUntouched(): void
    {
        $topic = TopicFactory::createOne(['images' => ['keep-me.png', 'and-me.png']]);

        $patched = $this->patch(self::endpoint() . "/{$topic->getId()}", [
            'json' => ['title' => 'Renamed'],
        ]);

        self::assertSame('Renamed', $patched['title']);
        self::assertSame(
            ['/storage/media/keep-me.png', '/storage/media/and-me.png'],
            $patched['images'],
        );
    }

    public function testStoredImageNamesNeverContainCommas(): void
    {
        $forum = ForumFactory::createOne();

        $topic = $this->post("/api/forums/{$forum->getId()}/topics", [
            'json' => [
                'title' => 'Comma Topic',
                'newImages' => [['filename' => 'a,b,c.png', 'data' => self::imageData()]],
            ],
        ]);

        self::assertStringNotContainsString(',', $topic['images'][0]);
    }

    public function testPostRejectsASingleImageObjectOnAListProperty(): void
    {
        $forum = ForumFactory::createOne();

        $response = $this->request('POST', "/api/forums/{$forum->getId()}/topics", [
            'json' => [
                'title' => 'Bad Payload',
                'newImages' => ['filename' => 'first.png', 'data' => self::imageData()],
            ],
        ]);

        self::assertSame(400, $response->getStatusCode());
        self::assertStringContainsString('"images" attribute', $response->toArray(false)['detail']);
    }

    public function testPostRejectsAnImageWithoutData(): void
    {
        $forum = ForumFactory::createOne();

        $response = $this->request('POST', "/api/forums/{$forum->getId()}/topics", [
            'json' => [
                'title' => 'Bad Payload',
                'newImages' => [['filename' => 'first.png']],
            ],
        ]);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testPostWithAnEmptyImageListStoresNoImages(): void
    {
        $forum = ForumFactory::createOne();

        $topic = $this->post("/api/forums/{$forum->getId()}/topics", [
            'json' => ['title' => 'No Images', 'newImages' => []],
        ]);

        self::assertSame([], $topic['images']);
    }

    private static function imageData(): string
    {
        return base64_encode((string) file_get_contents(TEST_DATA_DIR . '/forumify.png'));
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
