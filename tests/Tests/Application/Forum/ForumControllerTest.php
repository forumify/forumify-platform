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

class ForumControllerTest extends WebTestCase
{
    use UserTrait;
    use ACLTrait;

    public function testForum(): void
    {
        $client = static::createClient();

        /** @var ForumRepository $forumRepository */
        $forumRepository = self::getContainer()->get(ForumRepository::class);
        $forum1 = new Forum();
        $forum1->setTitle('Parent 1');

        $forum2 = new Forum();
        $forum2->setTitle('Parent 2');
        $forumRepository->saveAll([$forum1, $forum2]);

        $forum3 = new Forum();
        $forum3->setTitle('Child 1');
        $forum3->setParent($forum1);
        $forum1->getChildren()->add($forum3);

        $forum4 = new Forum();
        $forum4->setTitle('Child 2');
        $forum4->setParent($forum1);
        $forum1->getChildren()->add($forum4);
        $forumRepository->saveAll([$forum3, $forum4]);

        $this->createACL(Forum::class, $forum1->getId(), 'view');
        $this->createACL(Forum::class, $forum3->getId(), 'view');

        $user = $this->createUser();

        $client->loginUser($user);
        $crawler = $client->request('GET', '/forum');
        self::assertResponseIsSuccessful();

        // Only parent 1 is visible
        $forumLinks = $crawler->filter('a[href^="/forum"] h3');
        self::assertCount(1, $forumLinks);
        self::assertSame('Parent 1', $forumLinks->text());

        // only child 1 is visible
        $subForumLinks = $crawler->filter('.forum-subforums a[href^="/forum"]');
        self::assertCount(1, $subForumLinks);
        self::assertSame('Child 1', $subForumLinks->text());

        // enter parent 1, only child 1 is visible
        $crawler = $client->clickLink('Parent 1');
        $forumLinks = $crawler->filter('a[href^="/forum"] h3');
        self::assertCount(1, $forumLinks);
        self::assertSame('Child 1', $forumLinks->text());
    }
}
