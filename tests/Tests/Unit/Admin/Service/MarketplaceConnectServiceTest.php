<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Admin\Service;

use Forumify\Admin\Service\MarketplaceConnectService;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\HttpClientFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MarketplaceConnectServiceTest extends TestCase
{
    public function testIsConnectedSettings(): void
    {
        $settingRepo = $this->createMock(SettingRepository::class);
        $settingRepo
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn('blip')
        ;

        $service = $this->createService($settingRepo);
        self::assertTrue($service->isConnected());
    }

    public function testIsConnectedEnv(): void
    {
        $settingRepo = $this->createMock(SettingRepository::class);
        $settingRepo
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(null)
        ;

        $service = $this->createService($settingRepo, 'client_id', 'client_secret');
        self::assertTrue($service->isConnected());
    }

    public function testIsConnectedNull(): void
    {
        $settingRepo = $this->createMock(SettingRepository::class);
        $settingRepo
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(null)
        ;

        $service = $this->createService($settingRepo);
        self::assertFalse($service->isConnected());
    }

    private function createService(
        ?SettingRepository $settingRepository = null,
        ?string $clientId = null,
        ?string $clientSecret = null,
    ): MarketplaceConnectService {
        return new MarketplaceConnectService(
            $settingRepository ?? $this->createStub(SettingRepository::class),
            'https://forumify.net',
            $this->createStub(HttpClientFactory::class),
            $this->createStub(UrlGeneratorInterface::class),
            $clientId ?? '',
            $clientSecret ?? '',
        );
    }
}
