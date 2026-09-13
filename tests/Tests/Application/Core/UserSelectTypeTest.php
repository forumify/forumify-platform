<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Core;

use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Repository\MessageThreadRepository;
use Forumify\Testing\Factories\Core\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class UserSelectTypeTest extends WebTestCase
{
    use Factories;

    private const URL_ATTRIBUTE = 'data-symfony--ux-autocomplete--autocomplete-url-value';

    public function testFieldRendersWithoutPreloadedUsers(): void
    {
        $client = static::createClient();
        $client->loginUser(UserFactory::createOne());
        UserFactory::createMany(3);

        $crawler = $client->request('GET', '/messenger/create');
        self::assertResponseIsSuccessful();

        $select = $crawler->filter('select[name="new_message_thread[participants][]"]');
        self::assertCount(0, $select->filter('option'));
        self::assertStringStartsWith('/autocomplete/forumify_user_select', (string)$select->attr(self::URL_ATTRIBUTE));
        self::assertSame('false', $select->attr('data-symfony--ux-autocomplete--autocomplete-preload-value'));
        self::assertSame('Type to search for users', $select->attr('placeholder'));
    }

    public function testFieldRendersSelectedUsers(): void
    {
        $client = static::createClient();
        $client->loginUser(UserFactory::createOne());
        $recipient = UserFactory::createOne(['displayName' => 'Recipient']);

        $crawler = $client->request('GET', "/messenger/create?recipient={$recipient->getId()}");
        self::assertResponseIsSuccessful();

        $options = $crawler->filter('select[name="new_message_thread[participants][]"] option');
        self::assertCount(1, $options);
        self::assertSame((string)$recipient->getId(), $options->attr('value'));
        self::assertSame('Recipient', $options->text());
    }

    public function testSearchReturnsMatchingUsers(): void
    {
        $client = static::createClient();
        $currentUser = UserFactory::createOne(['username' => 'selectme_current']);
        $client->loginUser($currentUser);

        $match = UserFactory::createOne(['username' => 'selectme_match', 'displayName' => 'Match']);
        $byDisplayName = UserFactory::createOne(['username' => 'other', 'displayName' => 'SelectMe Display']);
        $banned = UserFactory::createOne(['username' => 'selectme_banned', 'banned' => true]);
        $unverified = UserFactory::createOne(['username' => 'selectme_unverified', 'emailVerified' => false]);
        UserFactory::createOne(['username' => 'unrelated']);

        $client->request('GET', '/messenger/create');
        $results = $this->search($client, 'new_message_thread[participants][]', 'selectme');

        self::assertEqualsCanonicalizing([
            ['value' => (string)$match->getId(), 'text' => 'Match'],
            ['value' => (string)$byDisplayName->getId(), 'text' => 'SelectMe Display'],
        ], $results);
        self::assertNotContains((string)$banned->getId(), array_column($results, 'value'));
        self::assertNotContains((string)$unverified->getId(), array_column($results, 'value'));
    }

    public function testSearchExcludesGivenUsers(): void
    {
        $client = static::createClient();
        $currentUser = UserFactory::createOne(['username' => 'participant_current']);
        $client->loginUser($currentUser);

        $participant = UserFactory::createOne(['username' => 'participant_existing']);
        $candidate = UserFactory::createOne(['username' => 'participant_candidate']);

        $thread = new MessageThread();
        $thread->setTitle('test');
        $thread->addParticipant($currentUser);
        $thread->addParticipant($participant);
        self::getContainer()->get(MessageThreadRepository::class)->save($thread);

        $client->request('GET', "/messenger/{$thread->getId()}/add-participant");
        $results = $this->search($client, 'form[participants][]', 'participant');

        self::assertSame([(string)$candidate->getId()], array_column($results, 'value'));
    }

    public function testSearchRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/autocomplete/forumify_user_select?query=a');

        self::assertResponseRedirects();
    }

    /**
     * @return list<array{value: string, text: string}>
     */
    private function search(KernelBrowser $client, string $fieldName, string $query): array
    {
        $url = (string)$client->getCrawler()->filter("select[name=\"$fieldName\"]")->attr(self::URL_ATTRIBUTE);
        $client->request('GET', $url . (str_contains($url, '?') ? '&' : '?') . http_build_query(['query' => $query]));
        self::assertResponseIsSuccessful();

        return json_decode((string)$client->getResponse()->getContent(), true)['results'];
    }
}
