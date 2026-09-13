<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Core;

use Forumify\Core\Service\ThemeService;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class ThemeControllerTest extends WebTestCase
{
    use Factories;

    public function testTogglingRemembersTheThemeBeyondTheSession(): void
    {
        $client = static::createClient();
        $client->request('GET', '/theme/toggle');

        $cookie = $client->getResponse()->headers->getCookies()[0] ?? null;

        self::assertNotNull($cookie);
        self::assertSame(ThemeService::CURRENT_THEME_COOKIE, $cookie->getName());
        self::assertSame('dark', $cookie->getValue());
        self::assertGreaterThan(time(), $cookie->getExpiresTime());
    }

    public function testTogglingBackKeepsTheExpiry(): void
    {
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie(
            ThemeService::CURRENT_THEME_COOKIE,
            'dark',
        ));

        $client->request('GET', '/theme/toggle');

        $cookie = $client->getResponse()->headers->getCookies()[0] ?? null;

        self::assertNotNull($cookie);
        self::assertSame('default', $cookie->getValue());
        self::assertGreaterThan(time(), $cookie->getExpiresTime());
    }
}
