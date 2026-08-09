<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\TimezoneResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class TimezoneResolverTest extends TestCase
{
    public function testUserTimezoneTakesPrecedenceOverDefault(): void
    {
        $user = new User();
        $user->setTimezone('Europe/Brussels');

        $resolver = $this->createResolver($user, 'America/New_York');

        self::assertEquals('Europe/Brussels', $resolver->getTimezone()->getName());
    }

    public function testUserWithoutTimezoneFallsBackToDefault(): void
    {
        $resolver = $this->createResolver(new User(), 'America/New_York');

        self::assertEquals('America/New_York', $resolver->getTimezone()->getName());
    }

    public function testGuestFallsBackToDefault(): void
    {
        $resolver = $this->createResolver(null, 'America/New_York');

        self::assertEquals('America/New_York', $resolver->getTimezone()->getName());
    }

    public function testFallsBackToUtcWhenNoDefaultConfigured(): void
    {
        $resolver = $this->createResolver(null, null);

        self::assertEquals('UTC', $resolver->getTimezone()->getName());
    }

    public function testInvalidTimezonesAreIgnored(): void
    {
        $user = new User();
        $user->setTimezone('Not/AZone');

        $resolver = $this->createResolver($user, 'Also/NotAZone');

        self::assertEquals('UTC', $resolver->getTimezone()->getName());
    }

    private function createResolver(?User $user, ?string $defaultTimezone): TimezoneResolver
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $settingRepository = $this->createMock(SettingRepository::class);
        $settingRepository->method('get')->willReturnMap([
            [TimezoneResolver::DEFAULT_TIMEZONE_SETTING, $defaultTimezone],
        ]);

        return new TimezoneResolver($security, $settingRepository);
    }
}
