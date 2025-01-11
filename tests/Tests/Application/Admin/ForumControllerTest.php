<?php

declare(strict_types=1);

namespace Application\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\UserTrait;

class ForumControllerTest extends WebTestCase
{
    use UserTrait;

    public function testForum(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = $this->createAdmin();
        $client->loginUser($admin);

        // Go to forum in admin
        $client->request('GET', '/admin/forum');

        // Create a new forum
        $client->clickLink('Create forum');
        $client->submitForm('Save', ['forum[title]' => 'F1']);

        // Save forum ACLs
        $crawler = $client->submitForm('Save');

        // Verify we're in the new forum
        self::assertSame('F1', $crawler->filter('#forum_title')->attr('value'));

        // Create a new forum group
        $client->clickLink('Create group');
        $client->submitForm('Save', ['forum_group[title]' => 'F1G1']);

        // Save group ACLs
        $crawler = $client->submitForm('Save');

        // Verify the group was created
        self::assertSame('F1G1', $crawler->filter('.card-title h4')->text());


        // Cleanup
        $crawler = $client->clickLink('index');

        $deleteBtn = $crawler->filter('a[href="/admin/forum/f1/delete"]')->link();
        $client->click($deleteBtn);

        self::assertResponseIsSuccessful();
    }
}

