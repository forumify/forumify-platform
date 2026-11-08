<?php

declare(strict_types=1);

namespace Application\Admin;

use Forumify\Core\Entity\AuditLog;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\AuditLogRepository;
use Forumify\Forum\Entity\Topic;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Traits\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Foundry\Test\Factories;

class AuditLogTableTest extends WebTestCase
{
    use Factories;
    use UserTrait;

    private const string MALICIOUS_TITLE = '<b class="injected">Bold</b> <img class="injected" src=x onerror="alert(1)">';

    public function testColumnWithoutRendererEscapesHtml(): void
    {
        $crawler = $this->openAuditLogsWithEntry(self::MALICIOUS_TITLE);

        self::assertCount(0, $crawler->filter('td .injected'));
        self::assertCount(1, $crawler->filter('td')->reduce(
            static fn (Crawler $cell): bool => trim($cell->text(normalizeWhitespace: false)) === self::MALICIOUS_TITLE,
        ));
    }

    public function testColumnWithRendererKeepsItsHtml(): void
    {
        $crawler = $this->openAuditLogsWithEntry('Harmless title', $username);

        self::assertGreaterThan(0, $crawler->filter("td > a[href$='/$username']")->count());
    }

    public function testRenderedUsernameIsEscaped(): void
    {
        $maliciousUsername = '<b class="injected">Bold';
        $crawler = $this->openAuditLogsWithEntry('Harmless title', $maliciousUsername);

        self::assertCount(0, $crawler->filter('td a .injected'));
        self::assertCount(1, $crawler->filter('td > a')->reduce(
            static fn (Crawler $link): bool => $link->text(normalizeWhitespace: false) === $maliciousUsername,
        ));
    }

    private function openAuditLogsWithEntry(string $targetName, ?string &$username = null): Crawler
    {
        $client = static::createClient();
        $admin = $this->createAdmin();
        self::assertInstanceOf(User::class, $admin);
        $actor = $username === null ? $admin : UserFactory::createOne(['username' => $username]);
        $username = $actor->getUsername();

        $log = new AuditLog();
        $log->user = $actor;
        $log->action = 'create';
        $log->targetEntityClass = Topic::class;
        $log->targetEntityId = '1';
        $log->targetName = $targetName;
        self::getContainer()->get(AuditLogRepository::class)->save($log);

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/admin/audit-logs');
        self::assertResponseIsSuccessful();

        return $crawler;
    }
}
