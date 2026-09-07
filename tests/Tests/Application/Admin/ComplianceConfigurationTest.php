<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Admin;

use Forumify\Core\Compliance\ComplianceMode;
use Forumify\Core\Compliance\ComplianceService;
use Forumify\Testing\Traits\SettingTrait;
use Forumify\Testing\Traits\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class ComplianceConfigurationTest extends WebTestCase
{
    use Factories;
    use SettingTrait;
    use UserTrait;

    public function testComplianceTabRendersEverySection(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());

        $crawler = $client->request('GET', '/admin/configuration');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('button[data-tab-id="compliance"]'));
        self::assertCount(1, $crawler->filter('#compliance[data-controller~="forumify--compliance"]'));

        $sections = $crawler->filter('#compliance [data-compliance-mode]');
        self::assertCount(3, $sections);
        self::assertSame(
            ['off', 'generated', 'cms_page'],
            $sections->each(fn ($section) => $section->attr('data-compliance-mode')),
        );
    }

    public function testModeSelectDrivesTheSections(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());

        $crawler = $client->request('GET', '/admin/configuration');
        $select = $crawler->filter('#configuration_forumify__compliance__mode');

        self::assertSame('mode', $select->attr('data-forumify--compliance-target'));
        self::assertSame('change->forumify--compliance#update', $select->attr('data-action'));
        self::assertSame(
            ['off', 'generated', 'cms_page'],
            $select->filter('option')->each(fn ($option) => $option->attr('value')),
        );
    }

    public function testSavingComplianceSettingsPublishesThePolicy(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->loginUser($this->createAdmin());
        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::Off->value);
        $this->setSetting('forumify.compliance.controller_name', null);

        $crawler = $client->request('GET', '/admin/configuration');
        $form = $crawler->selectButton('Save')->form();
        $form['configuration[forumify__compliance__mode]'] = ComplianceMode::Generated->value;
        $form['configuration[forumify__compliance__controller_name]'] = 'Example Forums BV';
        $crawler = $client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSame(
            ComplianceMode::Generated->value,
            $crawler->filter('#configuration_forumify__compliance__mode option[selected]')->attr('value'),
        );
        self::assertSame(
            'Example Forums BV',
            $crawler->filter('#configuration_forumify__compliance__controller_name')->attr('value'),
        );

        $crawler = $client->request('GET', '/privacy-policy');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Example Forums BV', $crawler->filter('body')->text());
        self::assertStringContainsString('Last updated', $crawler->filter('body')->text());
    }
}
