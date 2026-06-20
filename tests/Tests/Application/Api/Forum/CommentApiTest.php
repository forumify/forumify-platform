<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use Symfony\Component\HttpClient\Exception\ClientException;
use Tests\Tests\Application\Api\ApiTestCase;
use Tests\Tests\Application\Api\Crud\DeleteTestTrait;
use Tests\Tests\Application\Api\Crud\GetCollectionTestTrait;
use Tests\Tests\Application\Api\Crud\GetTestTrait;
use Tests\Tests\Application\Api\Crud\PatchTestTrait;
use Tests\Tests\Factories\Forum\CommentFactory;
use Tests\Tests\Factories\Forum\TopicFactory;
use Tests\Tests\Factories\OAuth\OAuthClientFactory;

class CommentApiTest extends ApiTestCase
{
    use GetTestTrait;
    use GetCollectionTestTrait;
    use PatchTestTrait;
    use DeleteTestTrait;

    protected static function factory(): string
    {
        return CommentFactory::class;
    }

    protected static function endpoint(): string
    {
        return '/api/forums/comments';
    }

    protected function getPatchBody(): array
    {
        return ['content' => 'This is some test content :-)'];
    }

    public function testGetNotAdmin(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();

        $comment = CommentFactory::createOne();

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);
        $this->get(self::endpoint() . '/' . $comment->getId());
    }

    public function testGetCollectionNotAdmin(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();

        CommentFactory::createMany(3);

        $response = $this->getCollection(self::endpoint());

        self::assertEmpty($response);
    }

    public function testGetCollectionOnTopic(): void
    {
        $comment1 = CommentFactory::createOne();
        $comment2 = CommentFactory::createOne();

        $response = $this->getCollection("/api/forums/topics/{$comment1->getTopic()->getId()}/comments");

        self::assertCount(1, $response);

        $allIds = array_column($response, 'id');
        self::assertContains($comment1->getId(), $allIds);
        self::assertNotContains($comment2->getId(), $allIds);
    }

    public function testPostOnTopic(): void
    {
        $topic = TopicFactory::createOne();

        $response = $this->post("/api/forums/topics/{$topic->getId()}/comments", [
            'json' => [
                'content' => 'Hello from tests!',
            ],
        ]);

        self::assertIsInt($response['id']);
        self::assertEquals("/api/forums/topics/{$topic->getId()}", $response['topic']);
    }
}
