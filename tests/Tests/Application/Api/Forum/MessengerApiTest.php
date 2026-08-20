<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Messenger;

use Symfony\Component\HttpClient\Exception\ClientException;
use Forumify\Testing\Api\ApiTestCase;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Factories\Forum\MessageFactory;
use Forumify\Testing\Factories\Forum\MessageThreadFactory;
use Forumify\Testing\Factories\OAuth\OAuthClientFactory;

class MessengerApiTest extends ApiTestCase
{
    public function testGet(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();
        $mt = MessageThreadFactory::createOne(['participants' => [$this->oauthClient->getUser()]]);

        $response = $this->get("/api/messenger/message-threads/{$mt->getId()}");

        self::assertResponseIsSuccessful();
        self::assertContains("/api/users/{$this->oauthClient?->getUserId()}", $response['participants']);
    }

    public function testGetNotInParticipants(): void
    {
        $mt = MessageThreadFactory::createOne();

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);

        $this->get("/api/messenger/message-threads/{$mt->getId()}");
    }

    public function testGetCollection(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();
        $mt1 = MessageThreadFactory::createOne(['participants' => [$this->oauthClient->getUser()]]);
        $mt2 = MessageThreadFactory::createOne(['participants' => []]);

        $response = $this->getCollection("/api/messenger/message-threads");
        $ids = array_column($response, 'id');

        self::assertContains($mt1->getId(), $ids);
        self::assertNotContains($mt2->getId(), $ids);
    }

    public function testPost(): void
    {
        $recipient = UserFactory::createOne();

        $response = $this->post('/api/messenger/message-threads', [
            'json' => [
                'title' => 'Hello Frens',
                'message' => 'This is a test message',
                'participants' => ["/api/users/{$recipient->getId()}"],
            ],
        ]);

        self::assertContains("/api/users/{$this->oauthClient?->getUserId()}", $response['participants']);
        self::assertContains("/api/users/{$recipient->getId()}", $response['participants']);
    }

    public function testPatch(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();
        $mt = MessageThreadFactory::createOne(['participants' => [$this->oauthClient->getUser()]]);

        $response = $this->patch("/api/messenger/message-threads/{$mt->getId()}", [
            'json' => ['title' => 'Test Thread'],
        ]);
        self::assertEquals('Test Thread', $response['title']);
    }

    public function testPatchNotInParticipants(): void
    {
        $mt = MessageThreadFactory::createOne();

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);

        $this->patch("/api/messenger/message-threads/{$mt->getId()}", [
            'json' => ['title' => 'Test Thread'],
        ]);
    }

    public function testGetMessage(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();
        $mt = MessageThreadFactory::createOne(['participants' => [$this->oauthClient->getUser()]]);

        $message = MessageFactory::createOne(['thread' => $mt]);

        $response = $this->get("/api/messenger/messages/{$message->getId()}");

        self::assertResponseIsSuccessful();
        self::assertArrayHasKey('content', $response);
    }

    public function testGetMessageNotThreadParticipant(): void
    {
        $mt = MessageThreadFactory::createOne();
        $message = MessageFactory::createOne(['thread' => $mt]);

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);

        $this->get("/api/messenger/messages/{$message->getId()}");
    }

    public function testGetMessages(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();
        $mt = MessageThreadFactory::createOne(['participants' => [$this->oauthClient->getUser()]]);

        $m1 = MessageFactory::createOne(['thread' => $mt]);
        $m2 = MessageFactory::createOne(['thread' => $mt]);
        $m3 = MessageFactory::createOne();

        $response = $this->getCollection("/api/messenger/message-threads/{$mt->getId()}/messages");

        $ids = array_column($response, 'id');
        self::assertContains($m1->getId(), $ids);
        self::assertContains($m2->getId(), $ids);
        self::assertNotContains($m3->getId(), $ids);
    }

    public function testGetMessagesNotThreadParticipant(): void
    {
        $m = MessageFactory::createOne();

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);

        $this->getCollection("/api/messenger/message-threads/{$m->getThread()->getId()}/messages");
    }

    public function testPostMessage(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();
        $mt = MessageThreadFactory::createOne(['participants' => [$this->oauthClient->getUser()]]);

        $response = $this->post("/api/messenger/message-threads/{$mt->getId()}/messages", [
            'json' => ['content' => 'Hello there!'],
        ]);

        self::assertResponseIsSuccessful();
        self::assertEquals('Hello there!', $response['content']);
    }

    public function testPostMessageNotParticipant(): void
    {
        $mt = MessageThreadFactory::createOne();

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);

        $this->post("/api/messenger/message-threads/{$mt->getId()}/messages", [
            'json' => ['content' => 'Hello there!'],
        ]);
    }

    public function testPatchMessage(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();
        $message = MessageFactory::createOne(['createdBy' => $this->oauthClient->getUser()]);

        $response = $this->patch("/api/messenger/messages/{$message->getId()}", [
            'json' => ['content' => 'Whoopsie, made a mistake!'],
        ]);
        self::assertEquals('Whoopsie, made a mistake!', $response['content']);
    }

    public function testPatchMessageNotCreator(): void
    {
        $message = MessageFactory::createOne();

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);

        $this->patch("/api/messenger/messages/{$message->getId()}", [
            'json' => ['content' => 'Whoopsie, made a mistake!'],
        ]);
    }
}
