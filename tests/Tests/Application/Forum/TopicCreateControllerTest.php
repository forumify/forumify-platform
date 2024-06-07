<?php

declare(strict_types=1);

namespace Application\Forum;

use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\RoleRepository;
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
        $client->request('GET', "/forum/{$forum->getSlug()}");
        self::assertResponseIsSuccessful();

        $client->clickLink('Post new topic');
        $client->submitForm('Post', [
            'topic[title]' => 'Test Topic',
            'topic[content]' => '<h1 id="test-topic">test</h1>'
        ]);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#test-topic', 'test');
    }
}
