<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumTag;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Repository\ForumTagRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\ACLTrait;
use Tests\Tests\Traits\UserTrait;

class TopicCreateWithTagsTest extends WebTestCase
{
    use UserTrait;
    use ACLTrait;

    private KernelBrowser $client;
    private Forum $forum;

    protected function setUp(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Test Forum');
        self::getContainer()->get(ForumRepository::class)->save($forum);
        $this->forum = $forum;

        $this->createACL(Forum::class, $forum->getId(), 'view');
        $this->createACL(Forum::class, $forum->getId(), 'create_topic');

        $user = $this->createUser();

        $client->loginUser($user);
        $this->client = $client;
    }

    public function testCreateWithForumTag(): void
    {
        $tag = new ForumTag();
        $tag->forum = $this->forum;
        $tag->title = 'Forum Tag';
        self::getContainer()->get(ForumTagRepository::class)->save($tag);

        $this->client->request('GET', "/forum/{$this->forum->getId()}/topic/create");
        $this->client->submitForm('Post', [
            'topic[title]' => 'Test Topic',
            'topic[content]' => '<h1 id="test-topic">test</h1>',
            'topic[tags]' => [$tag->getId()],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.tag', 'Forum Tag');
    }

    public function testCreateWithParentForumTag(): void
    {
        $childForum = new Forum();
        $childForum->setTitle('Child Forum');
        $childForum->setParent($this->forum);
        self::getContainer()->get(ForumRepository::class)->save($childForum);

        $tag = new ForumTag();
        $tag->forum = $this->forum;
        $tag->title = 'Parent Tag';
        $tag->allowInSubforums = true;
        self::getContainer()->get(ForumTagRepository::class)->save($tag);

        $this->createACL(Forum::class, $childForum->getId(), 'view');
        $this->createACL(Forum::class, $childForum->getId(), 'create_topic');

        $this->client->request('GET', "/forum/{$childForum->getId()}/topic/create");
        $this->client->submitForm('Post', [
            'topic[title]' => 'Test Topic',
            'topic[content]' => '<h1 id="test-topic">test</h1>',
            'topic[tags]' => [$tag->getId()],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.tag', 'Parent Tag');
    }

    public function testCreateWithGlobalTag(): void
    {
        $tag = new ForumTag();
        $tag->title = 'Global Tag';
        self::getContainer()->get(ForumTagRepository::class)->save($tag);

        $this->client->request('GET', "/forum/{$this->forum->getId()}/topic/create");
        $this->client->submitForm('Post', [
            'topic[title]' => 'Test Topic',
            'topic[content]' => '<h1 id="test-topic">test</h1>',
            'topic[tags]' => [$tag->getId()],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.tag', 'Global Tag');
    }

    public function testCreateWithDefaultForumTag(): void
    {
        $tag = new ForumTag();
        $tag->forum = $this->forum;
        $tag->title = 'Forum Tag';
        $tag->default = true;
        self::getContainer()->get(ForumTagRepository::class)->save($tag);

        $this->client->request('GET', "/forum/{$this->forum->getId()}/topic/create");
        $this->client->submitForm('Post', [
            'topic[title]' => 'Test Topic',
            'topic[content]' => '<h1 id="test-topic">test</h1>',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.tag', 'Forum Tag');
    }

    public function testCreateWithParentDefaultForumTag(): void
    {
        $childForum = new Forum();
        $childForum->setTitle('Child Forum');
        $childForum->setParent($this->forum);
        self::getContainer()->get(ForumRepository::class)->save($childForum);

        $tag = new ForumTag();
        $tag->forum = $this->forum;
        $tag->title = 'Parent Tag';
        $tag->allowInSubforums = true;
        $tag->default = true;
        self::getContainer()->get(ForumTagRepository::class)->save($tag);

        $this->createACL(Forum::class, $childForum->getId(), 'view');
        $this->createACL(Forum::class, $childForum->getId(), 'create_topic');

        $this->client->request('GET', "/forum/{$childForum->getId()}/topic/create");
        $this->client->submitForm('Post', [
            'topic[title]' => 'Test Topic',
            'topic[content]' => '<h1 id="test-topic">test</h1>',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.tag', 'Parent Tag');
    }

    public function testCreateWithDefaultGlobalTag(): void
    {
        $tag = new ForumTag();
        $tag->title = 'Global Tag';
        $tag->default = true;
        self::getContainer()->get(ForumTagRepository::class)->save($tag);

        $this->client->request('GET', "/forum/{$this->forum->getId()}/topic/create");
        $this->client->submitForm('Post', [
            'topic[title]' => 'Test Topic',
            'topic[content]' => '<h1 id="test-topic">test</h1>',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.tag', 'Global Tag');
    }
}
