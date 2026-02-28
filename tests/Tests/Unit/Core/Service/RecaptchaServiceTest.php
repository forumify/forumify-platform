<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Service;

use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\RecaptchaService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RecaptchaServiceTest extends TestCase
{
    public function testIsEnabledReturnsTrueWhenAllSettingsConfigured(): void
    {
        $settingRepository = $this->createMock(SettingRepository::class);
        $settingRepository->method('get')->willReturnMap([
            ['forumify.recaptcha.enabled', true],
            ['forumify.recaptcha.site_key', 'test-key'],
            ['forumify.recaptcha.site_secret', 'test-secret'],
        ]);

        $service = new RecaptchaService(
            $settingRepository,
            $this->createMock(HttpClientInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(RequestStack::class),
        );

        self::assertTrue($service->isEnabled());
    }

    public function testIsBotReturnsFalseWhenScoreIsAboveMinimum(): void
    {
        $settingRepository = $this->createMock(SettingRepository::class);
        $settingRepository->method('get')->willReturnMap([
            ['forumify.recaptcha.site_secret', 'test-secret'],
            ['forumify.recaptcha.min_score', 0.5],
        ]);

        $request = new Request(request: ['g-recaptcha-response' => 'valid-token']);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['score' => 0.9]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $service = new RecaptchaService(
            $settingRepository,
            $httpClient,
            $this->createMock(LoggerInterface::class),
            $requestStack,
        );

        self::assertFalse($service->isBot());
    }

    public function testIsBotReturnsTrueWhenScoreBelowMinimum(): void
    {
        $settingRepository = $this->createMock(SettingRepository::class);
        $settingRepository->method('get')->willReturnMap([
            ['forumify.recaptcha.site_secret', 'test-secret'],
            ['forumify.recaptcha.min_score', 0.8],
        ]);

        $request = new Request(request: ['g-recaptcha-response' => 'invalid-token']);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['score' => 0.3]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $service = new RecaptchaService(
            $settingRepository,
            $httpClient,
            $this->createMock(LoggerInterface::class),
            $requestStack,
        );

        self::assertTrue($service->isBot());
    }

    public function testGetJavascripts(): void
    {
        $service = new RecaptchaService(
            $this->createMock(SettingRepository::class),
            $this->createMock(HttpClientInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(RequestStack::class),
        );

        $javascripts = $service->getJavascripts('test-form');

        self::assertStringContainsString('google.com/recaptcha/api.js', $javascripts);
        self::assertStringContainsString('test-form', $javascripts);
        self::assertStringContainsString('grecaptchaCallback', $javascripts);
    }

    public function testModifyButtonHtml(): void
    {
        $settingRepository = $this->createMock(SettingRepository::class);
        $settingRepository->method('get')->willReturnMap([
            ['forumify.recaptcha.site_key', 'test-site-key'],
        ]);

        $service = new RecaptchaService(
            $settingRepository,
            $this->createMock(HttpClientInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(RequestStack::class),
        );

        $html = $service->modifyButtonHtml('<button type="submit">Submit</button>');

        self::assertStringContainsString('g-recaptcha', $html);
        self::assertStringContainsString('test-site-key', $html);
        self::assertStringContainsString('grecaptchaCallback', $html);
        self::assertStringContainsString('Submit', $html);
    }
}
