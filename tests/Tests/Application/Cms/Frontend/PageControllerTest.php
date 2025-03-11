<?php

declare(strict_types=1);

namespace Application\Cms\Frontend;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Repository\PageRepository;
use Forumify\Core\Entity\ACL;
use Forumify\Core\Repository\ACLRepository;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PageControllerTest extends WebTestCase
{
    public function testPage(): void
    {
        $client = static::createClient();

        $page = $this->createPage();
        $this->setupACL($page);

        $client->request('GET', '/test-page');
        self::assertSelectorTextContains('h1', 'Test page!');
    }

    public function testPageBuilder(): void
    {
        $client = static::createClient();

        $page = $this->createPage(Page::TYPE_BUILDER, '[
          {
            "widget": "layout.two_column",
            "settings": {
              "columnCount": "2",
              "columns": {
                "column1": "",
                "column2": ""
              }
            },
            "slots": [
              [
                {
                  "widget": "content.rich_text",
                  "settings": {
                    "content": "<p id=\"col-1\">This is column one</p>"
                  }
                }
              ],
              [
                {
                  "widget": "content.rich_text",
                  "settings": {
                    "content": "<p id=\"col-2\">This is column two</p>"
                  }
                }
              ]
            ]
          }
        ]');
        $this->setupACL($page);

        $client->request('GET', '/test-page');
        self::assertSelectorTextContains('#col-1', 'This is column one');
        self::assertSelectorTextContains('#col-2', 'This is column two');
    }

    public function testCss(): void
    {
        $client = static::createClient();

        $page = $this->createPage();
        $this->setupACL($page);

        $client->request('GET', '/page/test-page/css');

        $response = $client->getResponse();
        self::assertStringStartsWith('text/css;', $response->headers->get('Content-Type'));
        self::assertSame('h1 { color: red; }', $response->getContent());
    }

    public function testJs(): void
    {
        $client = static::createClient();

        $page = $this->createPage();
        $this->setupACL($page);

        $client->request('GET', '/page/test-page/javascript');

        $response = $client->getResponse();
        self::assertStringStartsWith('text/javascript;', $response->headers->get('Content-Type'));
        self::assertSame('console.log("Hello from test page!")', $response->getContent());
    }

    public function testNoAccess(): void
    {
        $client = static::createClient();

        $this->createPage();

        $client->request('GET', '/test-page');
        self::assertResponseStatusCodeSame(302);
        self::assertResponseHeaderSame('Location', 'http://localhost/login');
    }

    private function createPage(string $type = Page::TYPE_TWIG, ?string $content = null): Page
    {
        $page = new Page();
        $page->setTitle('Test Page');
        $page->setUrlKey('test-page');
        $page->setType($type);
        $page->setTwig($content ?? '<h1>Test page!</h1>');
        $page->setCss('h1 { color: red; }');
        $page->setJavascript('console.log("Hello from test page!")');

        self::getContainer()->get(PageRepository::class)->save($page);
        return $page;
    }

    private function setupACL(Page $page): void
    {
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
    }
}
