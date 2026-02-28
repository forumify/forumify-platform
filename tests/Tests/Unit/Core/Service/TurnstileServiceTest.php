<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Service;

use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\TurnstileService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TurnstileServiceTest extends TestCase
{
    public function testIsEnabledReturnsTrueWhenAllSettingsConfigured(): void
    {
        $settingRepository = $this->createMock(SettingRepository::class);
        $settingRepository->method('get')->willReturnMap([
            ['forumify.cf_turnstile.enabled', true],
            ['forumify.cf_turnstile.site_key', 'test-key'],
            ['forumify.cf_turnstile.site_secret', 'test-secret'],
        ]);

        $service = new TurnstileService(
            $settingRepository,
            $this->createMock(HttpClientInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(RequestStack::class),
        );

        self::assertTrue($service->isEnabled());
    }

    public function testIsBotReturnsFalseWhenTokenIsValid(): void
    {
        $settingRepository = $this->createMock(SettingRepository::class);
        $settingRepository->method('get')->willReturnMap([
            ['forumify.cf_turnstile.site_secret', 'test-secret'],
        ]);
        $request = new Request(request: ['cf-turnstile-response' => 'valid-token']);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['success' => true]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $result = new TurnstileService(
            $settingRepository,
            $httpClient,
            $this->createMock(LoggerInterface::class),
            $requestStack,
        )->isBot();

        self::assertFalse($result);
    }

    public function testGetJavascripts(): void
    {
        $service = new TurnstileService(
            $this->createMock(SettingRepository::class),
            $this->createMock(HttpClientInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(RequestStack::class),
        );

        $javascripts = $service->getJavascripts('test-form');

        self::assertStringContainsString('challenges.cloudflare.com/turnstile/v0/api.js', $javascripts);
        self::assertStringContainsString('test-form', $javascripts);
        self::assertStringContainsString('cfturnstileCallback', $javascripts);
    }

    public function testModifyButtonHtml(): void
    {
        $settingRepository = $this->createMock(SettingRepository::class);
        $settingRepository->method('get')->willReturnMap([
            ['forumify.cf_turnstile.site_key', 'test-site-key'],
        ]);

        $service = new TurnstileService(
            $settingRepository,
            $this->createMock(HttpClientInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(RequestStack::class),
        );

        $html = $service->modifyButtonHtml('<button type="submit">Submit</button>');

        self::assertStringContainsString('cf-turnstile', $html);
        self::assertStringContainsString('test-site-key', $html);
        self::assertStringContainsString('Submit', $html);
    }
}
