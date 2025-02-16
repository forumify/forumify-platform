<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Admin;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\UserTrait;

class ForumDeleteControllerTest extends WebTestCase
{
    use UserTrait;

    public function testDeleteChildForum(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = $this->createAdmin();
        $client->loginUser($admin);

        /** @var ForumRepository $forumRepository */
        $forumRepository = self::getContainer()->get(ForumRepository::class);

        $parent = new Forum();
        $parent->setType(Forum::TYPE_TEXT);
        $parent->setTitle('parent');

        $child = new Forum();
        $child->setType(Forum::TYPE_TEXT);
        $child->setTitle('child');
        $child->setParent($parent);

        $parent->getChildren()->add($child);
        $forumRepository->save($parent);

        $parentSlug = $parent->getSlug();
        $childSlug = $child->getSlug();

        // Delete the child forum
        $client->request('GET', "/admin/forum/{$childSlug}/delete?confirmed=1");
        self::assertResponseIsSuccessful();

        // Confirm the child was deleted by going to it, and being shown the index
        $crawler = $client->request('GET', "/admin/forum/{$childSlug}");
        self::assertResponseIsSuccessful();
        self::assertSame('index', $crawler->filter('h4')->first()->text());

        // Confirm the parent still exists
        $crawler = $client->request('GET', "/admin/forum/{$parentSlug}");
        self::assertResponseIsSuccessful();
        self::assertSame('parent', $crawler->filter('#forum_title')->attr('value'));
    }
}
