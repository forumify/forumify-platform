<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Core;

use Forumify\Core\EventSubscriber\PlatformInstallSubscriber;
use Forumify\Core\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InstallSubscriberTest extends WebTestCase
{
    private KernelBrowser $client;
    private SettingRepository $settingRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->settingRepository = self::getContainer()->get(SettingRepository::class);
        $this->settingRepository->set(PlatformInstallSubscriber::INSTALLED_SETTING, false);
    }

    protected function tearDown(): void
    {
        $this->settingRepository = self::getContainer()->get(SettingRepository::class);
        $this->settingRepository->set(PlatformInstallSubscriber::INSTALLED_SETTING, true);

        parent::tearDown();
    }

    public function testPlatformInstallSubscriber(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Save', [
            'form[forumName]' => 'Test Forum',
            'form[adminUser][username]' => 'admin_user',
            'form[adminUser][email]' => 'admin@forumify.net',
            'form[adminUser][password][first]' => 'test12345',
            'form[adminUser][password][second]' => 'test12345',
            'form[adminUser][timezone]' => 'UTC',
        ]);

        self::assertResponseIsSuccessful();
        self::assertAnySelectorTextContains('header span', 'Test Forum');
        self::assertAnySelectorTextContains('header a.btn-link span', 'admin_user');
    }
}
