<?php

declare(strict_types=1);

namespace Application\Cms\Frontend;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PageControllerTest extends WebTestCase
{
    public function testPage(): void
    {
        $client = static::createClient();

        $this->createPage();

        $client->request('GET', '/test-page');
        self::assertSelectorTextContains('h1', 'Test page!');
    }

    public function testCss(): void
    {
        $client = static::createClient();

        $this->createPage();

        $client->request('GET', '/page/test-page/css');

        $response = $client->getResponse();
        self::assertStringStartsWith('text/css;', $response->headers->get('Content-Type'));
        self::assertSame('h1 { color: red; }', $response->getContent());
    }

    public function testJs(): void
    {
        $client = static::createClient();

        $this->createPage();

        $client->request('GET', '/page/test-page/javascript');

        $response = $client->getResponse();
        self::assertStringStartsWith('text/javascript;', $response->headers->get('Content-Type'));
        self::assertSame('console.log("Hello from test page!")', $response->getContent());
    }

    private function createPage(): void
    {
        $page = new Page();
        $page->setTitle('Test Page');
        $page->setUrlKey('test-page');
        $page->setTwig('<h1>Test page!</h1>');
        $page->setCss('h1 { color: red; }');
        $page->setJavascript('console.log("Hello from test page!")');

        self::getContainer()->get(PageRepository::class)->save($page);
    }
}
