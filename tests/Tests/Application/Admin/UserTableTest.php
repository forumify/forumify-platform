<?php

declare(strict_types=1);

namespace Application\Admin;

use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Traits\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Foundry\Test\Factories;

class UserTableTest extends WebTestCase
{
    use Factories;
    use UserTrait;

    private const string MALICIOUS_USERNAME = '<b class="injected">Bold';
    private const string MALICIOUS_EMAIL = '<b class="injected">@example.com';

    public function testUsernameIsEscaped(): void
    {
        $crawler = $this->openUsersWith(['username' => self::MALICIOUS_USERNAME]);

        self::assertCount(0, $crawler->filter('td .injected'));
        self::assertGreaterThan(0, $this->cellsWithText($crawler, self::MALICIOUS_USERNAME)->count());
    }

    public function testBannedUsernameIsEscapedAndKeepsItsTag(): void
    {
        $crawler = $this->openUsersWith(['username' => self::MALICIOUS_USERNAME, 'banned' => true]);

        self::assertCount(0, $crawler->filter('td .injected'));
        self::assertCount(1, $crawler->filter('td > span.tag-error'));
    }

    public function testEmailIsEscaped(): void
    {
        $crawler = $this->openUsersWith(['email' => self::MALICIOUS_EMAIL]);

        self::assertCount(0, $crawler->filter('td .injected'));
        self::assertCount(1, $this->cellsWithText($crawler, self::MALICIOUS_EMAIL));
    }

    public function testUnverifiedEmailIsEscapedAndKeepsItsWarning(): void
    {
        $crawler = $this->openUsersWith(['email' => self::MALICIOUS_EMAIL, 'emailVerified' => false]);

        self::assertCount(0, $crawler->filter('td .injected'));
        self::assertCount(1, $crawler->filter('td > i.ph-warning'));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function openUsersWith(array $attributes): Crawler
    {
        $client = static::createClient();
        UserFactory::createOne($attributes);

        $client->loginUser($this->createAdmin());
        $crawler = $client->request('GET', '/admin/users');
        self::assertResponseIsSuccessful();

        return $crawler;
    }

    private function cellsWithText(Crawler $crawler, string $text): Crawler
    {
        return $crawler->filter('td')->reduce(
            static fn (Crawler $cell): bool => trim($cell->text(normalizeWhitespace: false)) === $text,
        );
    }
}
