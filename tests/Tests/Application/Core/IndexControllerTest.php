<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Core;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Repository\PageRepository;
use Forumify\Core\Entity\ACL;
use Forumify\Core\Repository\ACLRepository;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'No homepage defined');
    }

    public function testIndexPage(): void
    {
        $client = static::createClient();

        $page = new Page();
        $page->setUrlKey('');
        $page->setTitle('Index');
        $page->setTwig('<h1>Welcome from pages!</h1>');
        self::getContainer()->get(PageRepository::class)->save($page);

        $acl = new ACL();
        $acl->setEntity(Page::class);
        $acl->setEntityId((string)$page->getId());
        $acl->setPermission('view');

        /** @var RoleRepository $roleRepository */
        $roleRepository = self::getContainer()->get(RoleRepository::class);
        $acl->setRoles([
            $roleRepository->findOneBy(['slug' => 'guest']),
            $roleRepository->findOneBy(['slug' => 'user']),
        ]);

        self::getContainer()->get(ACLRepository::class)->save($acl);

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Welcome from pages!');
    }
}
