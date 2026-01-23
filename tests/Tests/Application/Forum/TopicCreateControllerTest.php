<?php

declare(strict_types=1);

namespace Application\Forum;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\ACLTrait;
use Tests\Tests\Traits\UserTrait;

class TopicCreateControllerTest extends WebTestCase
{
    use UserTrait;
    use ACLTrait;

    public function testCreateTopic(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Test Forum');
        self::getContainer()->get(ForumRepository::class)->save($forum);

        $this->createACL(Forum::class, $forum->getId(), 'view');
        $this->createACL(Forum::class, $forum->getId(), 'create_topic');

        $user = $this->createUser();

        $client->loginUser($user);
        $client->request('GET', "/forum/{$forum->getId()}/topic/create");
        $client->submitForm('Post', [
            'topic[title]' => 'Test Topic',
            'topic[content]' => '<h1 id="test-topic">test</h1>',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#test-topic', 'test');
    }

    public function testCannotCreateTopicWhenNotVerified(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Test Forum');
        self::getContainer()->get(ForumRepository::class)->save($forum);

        $this->createACL(Forum::class, $forum->getId(), 'create_topic');

        $user = $this->createUser();
        $user->setEmailVerified(false);

        $client->loginUser($user);
        $client->request('GET', "/forum/{$forum->getId()}/topic/create");

        self::assertResponseStatusCodeSame(403);
    }

    public function testCannotCreateTopicWhenNoACL(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Test Forum');
        self::getContainer()->get(ForumRepository::class)->save($forum);

        $user = $this->createUser();

        $client->loginUser($user);
        $client->request('GET', "/forum/{$forum->getId()}/topic/create");

        self::assertResponseStatusCodeSame(403);
    }
}
