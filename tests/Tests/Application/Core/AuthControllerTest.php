<?php

declare(strict_types=1);

namespace Application\Core;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\DTO\NewUser;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\CreateUserService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
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

        $this->setLoginMethod('username');
        $this->createTestUser();

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

        $this->setLoginMethod('email');
        $this->createTestUser();

        $client->request('GET', '/login');
        $crawler = $client->submitForm('Login', [
            '_username' => 'tester@example.org',
            '_password' => 'test12345'
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
        $settingRepository->set('core.enable_registrations', true);
        $settingRepository->set('core.recaptcha.enabled', false);

        $client->request('GET', '/register');
        $crawler = $client->submitForm('Register', [
            'register[username]' => 'tester',
            'register[email]' => 'tester@example.org',
            'register[password][first]' => 'test12345',
            'register[password][second]' => 'test12345'
        ]);

        $username = $crawler->filter('.header-menu')->text();
        self::assertStringContainsString('tester', $username);
        self::assertSelectorExists('a[href="/verify-email"]');
    }

    private function createTestUser(): User
    {
        /** @var CreateUserService $createUserService */
        $createUserService = self::getContainer()->get(CreateUserService::class);

        $user = new NewUser();
        $user->setUsername('tester');
        $user->setEmail('tester@example.org');
        $user->setPassword('test12345');

        return $createUserService->createUser($user, false);
    }

    private function setLoginMethod(string $method): void
    {
        /** @var SettingRepository $settingRepository */
        $settingRepository = self::getContainer()->get(SettingRepository::class);
        $settingRepository->set('core.enable_email_login', $method);
    }
}
