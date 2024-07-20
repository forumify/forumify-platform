<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Core;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Repository\PageRepository;
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

        $pageRepository = self::getContainer()->get(PageRepository::class);
        $page = new Page();
        $page->setUrlKey('');
        $page->setTitle('Index');
        $page->setTwig('<h1>Welcome from pages!</h1>');
        $pageRepository->save($page);

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Welcome from pages!');
    }
}
