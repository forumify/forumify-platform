<?php

declare(strict_types=1);

namespace Application\Forum;

use Forumify\Forum\Entity\Forum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\ACLTrait;
use Tests\Tests\Traits\ForumTrait;
use Tests\Tests\Traits\UserTrait;

class ForumControllerTest extends WebTestCase
{
    use UserTrait;
    use ForumTrait;
    use ACLTrait;

    public function testForum(): void
    {
        $client = static::createClient();

        $forum1 = $this->createForum('Parent 1');
        $this->createForum('Parent 2');

        $forum3 = $this->createForum('Child 1', $forum1);
        $forum1->getChildren()->add($forum3);

        $forum4 = $this->createForum('Child 2', $forum1);
        $forum1->getChildren()->add($forum4);

        $this->createACL(Forum::class, $forum1->getId(), 'view');
        $this->createACL(Forum::class, $forum3->getId(), 'view');

        $user = $this->createUser();

        $client->loginUser($user);
        $crawler = $client->request('GET', '/forum');
        self::assertResponseIsSuccessful();

        // Only parent 1 is visible
        $forumLinks = $crawler->filter('h3 a[href^="/forum"]');
        self::assertCount(1, $forumLinks);
        self::assertSame('Parent 1', $forumLinks->text());

        // only child 1 is visible
        $subForumLinks = $crawler->filter('.forum-subforums a[href^="/forum"]');
        self::assertCount(1, $subForumLinks);
        self::assertSame('Child 1', $subForumLinks->text());

        // enter parent 1, only child 1 is visible
        $crawler = $client->clickLink('Parent 1');
        $forumLinks = $crawler->filter('h3 a[href^="/forum"]');
        self::assertCount(1, $forumLinks);
        self::assertSame('Child 1', $forumLinks->text());
    }
}
