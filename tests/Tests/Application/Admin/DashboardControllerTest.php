<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Admin;

use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\RoleRepository;
use Forumify\Core\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\SettingTrait;
use Tests\Tests\Traits\UserTrait;

class DashboardControllerTest extends WebTestCase
{
    use UserTrait;
    use SettingTrait;

    public function testDashboard(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $this->setSetting('forumify.title', 'dashboard test');
        $user = $this->createAdmin('superadmin', 'superadmin@test.org');
        $client->loginUser($user);

        $client->request('GET', '/admin/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'dashboard test');
        self::assertSelectorTextContains('h1 + p', 'Welcome, superadmin');

        $role = new Role();
        $role->setTitle('admin');
        $role->setAdministrator(true);
        self::getContainer()->get(RoleRepository::class)->save($role);

        $user = $this->createUser('admin', 'admin@test.org');
        $user->addRoleEntity($role);
        self::getContainer()->get(UserRepository::class)->save($user);

        $client->loginUser($user);

        $client->request('GET', '/admin/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'dashboard test');
        self::assertSelectorTextContains('h1 + p', 'Welcome, admin');
    }
}
