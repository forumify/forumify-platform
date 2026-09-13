<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use Forumify\Forum\Entity\Forum;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Factories\Forum\CommentFactory;
use Forumify\Testing\Factories\Forum\ForumFactory;
use Forumify\Testing\Factories\Forum\TopicFactory;
use Forumify\Testing\Traits\ACLTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class ProfileControllerTest extends WebTestCase
{
    use ACLTrait;
    use Factories;

    public function testActivityRendersWhenTopicHasNoFirstComment(): void
    {
        $client = static::createClient();

        $forum = ForumFactory::createOne();
        $this->createACL(Forum::class, $forum->getId(), 'view');

        $user = UserFactory::createOne(['username' => 'author', 'displayName' => 'The Author']);
        $topic = TopicFactory::createOne(['forum' => $forum]);
        CommentFactory::createOne(['topic' => $topic, 'createdBy' => $user]);

        self::assertNull($topic->getFirstComment());

        $client->loginUser(UserFactory::createOne());
        $client->request('GET', '/profile/author');
        self::assertResponseIsSuccessful();
    }
}
