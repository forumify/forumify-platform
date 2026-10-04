<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Admin;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Forumify\Forum\Form\AccountSettingsType;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Traits\SettingTrait;
use Forumify\Testing\Traits\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class UserControllerTest extends WebTestCase
{
    use Factories;
    use SettingTrait;
    use UserTrait;

    public function testAdminCanEditProfileFieldsWhenUserEditingIsDisabled(): void
    {
        $client = static::createClient();
        $this->setSetting(AccountSettingsType::SETTING_DISABLE_DISPLAY_NAME_EDITING, true);
        $this->setSetting(AccountSettingsType::SETTING_DISABLE_SIGNATURE_EDITING, true);
        $user = UserFactory::createOne(['displayName' => 'Old Name', 'signature' => '<p>Old signature</p>']);
        $client->loginUser($this->createAdmin());

        $crawler = $client->request('GET', "/admin/users/{$user->getId()}/edit");
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $form['user[displayName]'] = 'New Name';
        $form['user[signature]'] = '<p>New signature</p>';
        $client->submit($form);
        self::assertResponseRedirects();

        $user = self::getContainer()->get(UserRepository::class)->find($user->getId());
        self::assertInstanceOf(User::class, $user);
        self::assertSame('New Name', $user->getDisplayName());
        self::assertSame('<p>New signature</p>', $user->getSignature());
    }
}
