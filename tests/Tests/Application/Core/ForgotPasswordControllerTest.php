<?php

declare(strict_types=1);

namespace Application\Core;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Forumify\Testing\Traits\UserTrait;

class ForgotPasswordControllerTest extends WebTestCase
{
    use UserTrait;

    public function testForgotPasswordUserNotFound(): void
    {
        $client = static::createClient();

        $this->runTest($client, 'blipblop');
    }

    public function testForgotPasswordUserFound(): void
    {
        $client = static::createClient();

        $this->createUser('tester', 'tester@example.org');
        $this->runTest($client, 'tester@example.org');
    }

    private function runTest(KernelBrowser $client, string $query): void
    {
        $client->followRedirects();
        $client->request('GET', '/forgot-password');
        $client->submitForm('Search', [
            'form[query]' => $query,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('p', 'Please check your inbox.');
    }
}
