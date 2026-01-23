<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Admin;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\UserTrait;

class ForumTagControllerTest extends WebTestCase
{
    use UserTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->followRedirects();
        $this->client->loginUser($this->createAdmin());
    }

    public function testForumTags(): void
    {
        $c = $this->client->request('GET', '/admin/forum-tags');

        $addBtn = $c->filter('a[aria-label="New forum tag"]')->link();
        $this->client->click($addBtn);

        $c = $this->client->submitForm('Save', [
            'forum_tag[title]' => 'blip tag',
            'forum_tag[color]' => '#ffffff',
            'forum_tag[default]' => false,
        ]);

        self::assertResponseIsSuccessful();
        self::assertAnySelectorTextContains('span.tag', 'blip tag');

        $editBtn = $c->filter('i.ph-pencil-simple-line')->ancestors()->first()->link();
        $this->client->click($editBtn);

        $c = $this->client->submitForm('Save', [
            'forum_tag[title]' => 'blop tag',
        ]);

        self::assertResponseIsSuccessful();
        self::assertAnySelectorTextContains('span.tag', 'blop tag');

        $deleteBtn = $c->filter('i.ph-x')->ancestors()->first()->link();
        $this->client->click($deleteBtn);
        $this->client->clickLink('Confirm');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('tbody tr', 'There are no records to display.');
    }
}
