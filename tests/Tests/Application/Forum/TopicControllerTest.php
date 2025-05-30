<?php

declare(strict_types=1);

namespace Application\Forum;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Form\TopicData;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Repository\TopicRepository;
use Forumify\Forum\Service\CreateTopicService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\ACLTrait;
use Tests\Tests\Traits\UserTrait;

class TopicControllerTest extends WebTestCase
{
    use UserTrait;
    use ACLTrait;

    public function testTopic(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Test Forum');
        self::getContainer()->get(ForumRepository::class)->save($forum);

        $topic = new TopicData();
        $topic->setTitle('Test Topic');
        $topic->setContent('<h1>Test Topic</h1>');
        $topic = self::getContainer()->get(CreateTopicService::class)->createTopic($forum, $topic);

        $this->createACL(Forum::class, $forum->getId(), 'view');
        $this->createACL(Forum::class, $forum->getId(), 'create_comment');

        $client->loginUser($this->createUser());
        $client->request('GET', "/topic/{$topic->getSlug()}");
        self::assertResponseIsSuccessful();

        $client->submitForm('Post comment', [
            'new_comment[content]' => '<p id="test-comment">Test Comment</p>',
        ]);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#test-comment');
    }

    public function testTopicNoACL(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Test Forum');
        self::getContainer()->get(ForumRepository::class)->save($forum);

        $topic = new TopicData();
        $topic->setTitle('Test Topic');
        $topic->setContent('<h1>Test Topic</h1>');
        $topic = self::getContainer()->get(CreateTopicService::class)->createTopic($forum, $topic);

        $client->loginUser($this->createUser());
        $client->request('GET', "/topic/{$topic->getSlug()}");

        self::assertResponseStatusCodeSame(403);
    }

    public function testTopicHidden(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Test Forum');
        self::getContainer()->get(ForumRepository::class)->save($forum);

        $this->createACL(Forum::class, $forum->getId(), 'view');

        $topic = new TopicData();
        $topic->setTitle('Test Topic');
        $topic->setContent('<h1>Test Topic</h1>');
        $topic = self::getContainer()->get(CreateTopicService::class)->createTopic($forum, $topic);
        $topic->setHidden(true);
        self::getContainer()->get(TopicRepository::class)->save($topic);

        $client->loginUser($this->createUser());
        $client->request('GET', "/topic/{$topic->getSlug()}");

        self::assertResponseStatusCodeSame(403);
    }

    public function testTopicOnlyShowOwn(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Test Forum');
        $forum->getDisplaySettings()->setOnlyShowOwnTopics(true);
        self::getContainer()->get(ForumRepository::class)->save($forum);

        $this->createACL(Forum::class, $forum->getId(), 'view');

        $author = $this->createUser();
        $topic = new TopicData();
        $topic->setTitle('Test Topic');
        $topic->setContent('<h1>Test Topic</h1>');
        $topic->setAuthor($author);
        $topic = self::getContainer()->get(CreateTopicService::class)->createTopic($forum, $topic);

        $client->loginUser($this->createUser('tester2', 'tester2@example.com'));
        $client->request('GET', "/topic/{$topic->getSlug()}");

        self::assertResponseStatusCodeSame(403);
    }
}
