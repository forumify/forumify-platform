<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Core;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Forumify\Forum\Form\AccountSettingsType;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Traits\SettingTrait;
use Forumify\Testing\Traits\UserTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class AccountSettingsControllerTest extends WebTestCase
{
    use Factories;
    use SettingTrait;
    use UserTrait;

    public function testUserCanChangeProfileFields(): void
    {
        $client = static::createClient();
        $this->setProfileEditingDisabled(displayName: false, signature: false);
        $user = UserFactory::createOne(['displayName' => 'Old Name', 'signature' => '<p>Old signature</p>']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/settings');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $form['account_settings[displayName]'] = 'New Name';
        $form['account_settings[signature]'] = '<p>New signature</p>';
        $client->submit($form);
        self::assertResponseRedirects();

        $user = $this->findUser($user->getId());
        self::assertSame('New Name', $user->getDisplayName());
        self::assertSame('<p>New signature</p>', $user->getSignature());
    }

    public function testProfileFieldsAreNotEditableWhenDisabled(): void
    {
        $client = static::createClient();
        $this->setProfileEditingDisabled(displayName: true, signature: true);
        $user = UserFactory::createOne(['displayName' => 'Managed Name', 'signature' => '<p>Managed signature</p>']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/settings');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('[name="account_settings[displayName]"]'));
        self::assertCount(0, $crawler->filter('[name="account_settings[signature]"]'));

        $form = $crawler->selectButton('Save')->form();
        $client->submit($form);
        self::assertResponseRedirects();

        $user = $this->findUser($user->getId());
        self::assertSame('Managed Name', $user->getDisplayName());
        self::assertSame('<p>Managed signature</p>', $user->getSignature());
    }

    /**
     * @return iterable<string, array{bool, bool}>
     */
    public static function provideIndependentSettings(): iterable
    {
        yield 'only display name disabled' => [true, false];
        yield 'only signature disabled' => [false, true];
    }

    #[DataProvider('provideIndependentSettings')]
    public function testProfileSettingsAreIndependent(bool $displayName, bool $signature): void
    {
        $client = static::createClient();
        $this->setProfileEditingDisabled($displayName, $signature);
        $client->loginUser(UserFactory::createOne());

        $crawler = $client->request('GET', '/settings');
        self::assertResponseIsSuccessful();
        self::assertCount($displayName ? 0 : 1, $crawler->filter('[name="account_settings[displayName]"]'));
        self::assertCount($signature ? 0 : 1, $crawler->filter('[name="account_settings[signature]"]'));
    }

    public function testAdminCanDisableProfileEditing(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->loginUser($this->createAdmin());
        $this->setProfileEditingDisabled(displayName: false, signature: false);

        $crawler = $client->request('GET', '/admin/configuration');
        $form = $crawler->selectButton('Save')->form();
        $form['configuration[forumify__disable_display_name_editing]']->tick();
        $form['configuration[forumify__disable_signature_editing]']->tick();
        $crawler = $client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#configuration_forumify__disable_display_name_editing[checked]'));
        self::assertCount(1, $crawler->filter('#configuration_forumify__disable_signature_editing[checked]'));
    }

    private function setProfileEditingDisabled(bool $displayName, bool $signature): void
    {
        $this->setSetting(AccountSettingsType::SETTING_DISABLE_DISPLAY_NAME_EDITING, $displayName);
        $this->setSetting(AccountSettingsType::SETTING_DISABLE_SIGNATURE_EDITING, $signature);
    }

    private function findUser(int $id): User
    {
        $user = self::getContainer()->get(UserRepository::class)->find($id);
        self::assertInstanceOf(User::class, $user);

        return $user;
    }
}
