<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Forumify\Testing\Traits\ACLTrait;
use Forumify\Testing\Traits\UserTrait;

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

    public function testCreateTopicWithImage(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Image Forum');
        $forum->setType(Forum::TYPE_IMAGE);
        self::getContainer()->get(ForumRepository::class)->save($forum);

        $this->createACL(Forum::class, $forum->getId(), 'view');
        $this->createACL(Forum::class, $forum->getId(), 'create_topic');

        $client->loginUser($this->createUser());
        $client->request('GET', "/forum/{$forum->getId()}/topic/create");
        $client->submitForm('Post', [
            'topic[title]' => 'Topic With Image',
            'topic[content]' => '<p>body</p>',
            'topic[image][file]' => new UploadedFile(TEST_DATA_DIR . '/forumify.png', 'forumify.png', 'image/png', null, true),
        ]);

        self::assertResponseIsSuccessful();

        $topic = self::getContainer()->get(TopicRepository::class)->findOneBy(['title' => 'Topic With Image']);
        self::assertNotNull($topic);
        self::assertCount(1, $topic->getImages());
        self::assertStringEndsWith('.png', $topic->getImage());
        self::assertSelectorExists("img.topic-image[src=\"/storage/media/{$topic->getImage()}\"]");
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
