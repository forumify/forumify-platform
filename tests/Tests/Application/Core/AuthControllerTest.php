<?php

declare(strict_types=1);

namespace Application\Core;

use Forumify\Core\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\SettingTrait;
use Tests\Tests\Traits\UserTrait;

class AuthControllerTest extends WebTestCase
{
    use UserTrait;
    use SettingTrait;

    public function testLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="_username"]');
        self::assertSelectorExists('input[name="_password"]');
    }

    public function testLoginUsername(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $this->setSetting('forumify.login_method', 'username');
        $this->createUser('tester', 'tester@example.org', 'test12345');

        $client->request('GET', '/login');
        $crawler = $client->submitForm('Login', [
            '_username' => 'tester',
            '_password' => 'test12345',
        ]);

        $username = $crawler->filter('.header-menu')->text();
        self::assertStringContainsString('tester', $username);
    }

    public function testLoginEmail(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $this->setSetting('forumify.login_method', 'email');
        $this->createUser('tester', 'tester@example.org', 'test12345');

        $client->request('GET', '/login');
        $crawler = $client->submitForm('Login', [
            '_username' => 'tester@example.org',
            '_password' => 'test12345',
        ]);

        $username = $crawler->filter('.header-menu')->text();
        self::assertStringContainsString('tester', $username);
    }

    public function testLoginUserNotExist(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/login');

        $client->submitForm('Login', [
            '_username' => 'not-a-real-user',
            '_password' => 'not-a-real-password',
        ]);

        self::assertSelectorTextContains('.alert-error', 'Invalid credentials.');
    }

    public function testLogout(): void
    {
        // re-use login test since $client->loginUser isn't log-out-able.
        $this->testLoginUsername();
        $client = static::getClient();

        $client->request('GET', '/logout');
        self::assertSelectorExists('a[href^="/login"]');
    }

    public function testRegister(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        /** @var SettingRepository $settingRepository */
        $settingRepository = self::getContainer()->get(SettingRepository::class);
        $settingRepository->set('forumify.enable_registrations', true);
        $settingRepository->set('forumify.recaptcha.enabled', false);

        $client->request('GET', '/register');
        $crawler = $client->submitForm('Register', [
            'register[username]' => 'tester',
            'register[email]' => 'tester@example.org',
            'register[password][first]' => 'test12345',
            'register[password][second]' => 'test12345',
            'register[timezone]' => 'UTC',
        ]);

        $username = $crawler->filter('.header-menu')->text();
        self::assertStringContainsString('tester', $username);
        self::assertSelectorExists('a[href="/verify-email"]');
    }
}
