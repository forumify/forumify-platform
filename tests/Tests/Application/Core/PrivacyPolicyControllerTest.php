<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Core;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Repository\PageRepository;
use Forumify\OAuth\Entity\IdentityProvider;
use Forumify\OAuth\Repository\IdentityProviderRepository;
use Forumify\Core\Compliance\ComplianceMode;
use Forumify\Core\Compliance\ComplianceService;
use Forumify\Testing\Traits\SettingTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;

class PrivacyPolicyControllerTest extends WebTestCase
{
    use Factories;
    use SettingTrait;

    public function testDisabledReturnsNotFound(): void
    {
        $client = static::createClient();
        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::Off->value);

        $client->request('GET', '/privacy-policy');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGeneratedPolicyListsDisclosures(): void
    {
        $client = static::createClient();
        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::Generated->value);
        $this->setSetting('forumify.compliance.controller_name', 'Example Forums BV');

        $crawler = $client->request('GET', '/privacy-policy');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Example Forums BV', $crawler->filter('body')->text());
        self::assertStringContainsString('What you post', $crawler->filter('body')->text());
        self::assertStringContainsString('REMEMBERME', $crawler->filter('body')->text());
    }

    public function testGeneratedPolicyWarnsWhenControllerIsNotConfigured(): void
    {
        $client = static::createClient();
        $this->setSetting('forumify.compliance.controller_name', null);
        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::Generated->value);

        $crawler = $client->request('GET', '/privacy-policy');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.alert-warning'));
    }

    public function testRecipientsAreOnlyListedWhenTheFeatureIsEnabled(): void
    {
        $client = static::createClient();
        $this->setSetting('forumify.cf_turnstile.enabled', false);
        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::Generated->value);

        $crawler = $client->request('GET', '/privacy-policy');
        self::assertStringNotContainsString('Cloudflare Turnstile', $crawler->filter('body')->text());

        $this->setSetting('forumify.cf_turnstile.enabled', true);

        $crawler = $client->request('GET', '/privacy-policy');
        self::assertStringContainsString('Cloudflare Turnstile', $crawler->filter('body')->text());
    }

    public function testCmsPageModeRedirects(): void
    {
        $client = static::createClient();
        $page = $this->createPage();

        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::CmsPage->value);
        $this->setSetting(ComplianceService::SETTING_CMS_PAGE, $page->getId());

        $client->request('GET', '/privacy-policy');

        self::assertResponseRedirects('/legal/privacy');
    }

    public function testCmsPageModeIsNotFoundWithoutAPage(): void
    {
        $client = static::createClient();
        $this->setSetting(ComplianceService::SETTING_CMS_PAGE, null);
        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::CmsPage->value);

        $client->request('GET', '/privacy-policy');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testLinkToPolicyWhenEnabled(): void
    {
        $client = static::createClient();
        $this->setSetting('forumify.enable_registrations', true);
        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::Off->value);

        $crawler = $client->request('GET', '/register');
        self::assertCount(0, $crawler->filter('form a[href="/privacy-policy"]'));
        self::assertCount(0, $crawler->filter('footer a[href="/privacy-policy"]'));

        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::Generated->value);

        $crawler = $client->request('GET', '/register');
        self::assertCount(1, $crawler->filter('form a[href="/privacy-policy"]'));
        self::assertCount(1, $crawler->filter('footer a[href="/privacy-policy"]'));

        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::CmsPage->value);
        $this->setSetting(ComplianceService::SETTING_CMS_PAGE, $this->createPage()->getId());

        $crawler = $client->request('GET', '/register');
        self::assertCount(1, $crawler->filter('form a[href="/privacy-policy"]'));
        self::assertCount(1, $crawler->filter('footer a[href="/privacy-policy"]'));
    }

    public function testIdentityProvidersDiscloseTheirOwnPrivacyDetails(): void
    {
        $client = static::createClient();
        $this->setSetting(ComplianceService::SETTING_MODE, ComplianceMode::Generated->value);
        $this->createIdentityProvider('Discord', 'discord');
        $this->createIdentityProvider('Company SSO', 'custom-saml');

        $crawler = $client->request('GET', '/privacy-policy');
        $text = $crawler->filter('body')->text();

        self::assertCount(1, $crawler->filter('a[href="https://discord.com/privacy"]'));
        self::assertStringContainsString('Discord', $text);
        self::assertStringNotContainsString('Company SSO', $text);

        $transferNotice = $crawler
            ->filter('p')
            ->reduce(fn ($node) => str_contains($node->text(), 'European Economic Area'))
            ->text();

        self::assertStringContainsString('Discord', $transferNotice);
        self::assertStringNotContainsString('Company SSO', $transferNotice);
    }

    private function createIdentityProvider(string $name, string $type): void
    {
        $identityProvider = new IdentityProvider();
        $identityProvider->setName($name);
        $identityProvider->setType($type);

        self::getContainer()->get(IdentityProviderRepository::class)->save($identityProvider);
    }

    private function createPage(): Page
    {
        $page = new Page();
        $page->setTitle('Privacy');
        $page->setUrlKey('legal/privacy');
        $page->setTwig('privacy');

        $pageRepository = self::getContainer()->get(PageRepository::class);
        $pageRepository->save($page);

        return $page;
    }
}
