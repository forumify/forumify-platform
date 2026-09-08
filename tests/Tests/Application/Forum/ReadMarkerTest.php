<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use Forumify\Forum\Entity\Forum;
use Forumify\Testing\Factories\Core\ACLFactory;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Factories\Forum\CommentFactory;
use Forumify\Testing\Factories\Forum\ForumFactory;
use Forumify\Testing\Factories\Forum\TopicFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Foundry\Test\Factories;

class ReadMarkerTest extends WebTestCase
{
    use Factories;

    public function testMarkingAForumAsReadOnlyAffectsItsOwnTree(): void
    {
        $client = static::createClient();

        $parentA = ForumFactory::createOne(['title' => 'Parent A']);
        $childB = ForumFactory::createOne(['title' => 'Child B', 'parent' => $parentA]);
        $invisibleE = ForumFactory::createOne(['title' => 'Invisible E', 'parent' => $parentA]);
        $grandChildF = ForumFactory::createOne(['title' => 'Grand child F', 'parent' => $invisibleE]);
        $parentC = ForumFactory::createOne(['title' => 'Parent C']);
        $childD = ForumFactory::createOne(['title' => 'Child D', 'parent' => $parentC]);

        foreach ([$parentA, $childB, $grandChildF, $parentC, $childD] as $forum) {
            ACLFactory::createOne([
                'entity' => Forum::class,
                'entityId' => (string)$forum->getId(),
                'permission' => 'view',
            ]);
        }

        $topicB = TopicFactory::createOne(['title' => 'Topic in B', 'forum' => $childB]);
        $topicC = TopicFactory::createOne(['title' => 'Topic in C', 'forum' => $parentC]);
        $topicD = TopicFactory::createOne(['title' => 'Topic in D', 'forum' => $childD]);
        $topicE = TopicFactory::createOne(['title' => 'Topic in E', 'forum' => $invisibleE]);
        $topicF = TopicFactory::createOne(['title' => 'Topic in F', 'forum' => $grandChildF]);
        foreach ([$topicB, $topicC, $topicD, $topicE, $topicF] as $topic) {
            CommentFactory::createOne(['topic' => $topic]);
        }

        $client->loginUser(UserFactory::createOne());

        $crawler = $client->request('GET', '/forum');
        self::assertResponseIsSuccessful();

        self::assertSame('unread', $this->markerState($crawler, 'forum', $parentA->getId()));
        self::assertSame('unread', $this->markerState($crawler, 'forum', $childB->getId()));
        self::assertSame('unread', $this->markerState($crawler, 'forum', $parentC->getId()));
        self::assertSame('unread', $this->markerState($crawler, 'forum', $childD->getId()));
        self::assertCount(0, $crawler->filter(sprintf(
            '[data-read-marker-type="forum"][data-read-marker-id="%d"]',
            $invisibleE->getId(),
        )));

        $markers = [
            ['type' => 'forum', 'id' => $parentA->getId()],
            ['type' => 'forum', 'id' => $childB->getId()],
            ['type' => 'forum', 'id' => $parentC->getId()],
            ['type' => 'forum', 'id' => $childD->getId()],
            ['type' => 'forum', 'id' => $grandChildF->getId()],
            ['type' => 'topic', 'id' => $topicB->getId()],
            ['type' => 'topic', 'id' => $topicD->getId()],
            ['type' => 'nonsense', 'id' => $childB->getId()],
        ];

        $states = $this->markAsRead($client, ['type' => 'forum', 'id' => $childB->getId()], $markers);

        self::assertArrayNotHasKey('nonsense:' . $childB->getId(), $states);
        self::assertTrue($states['forum:' . $childB->getId()]);
        self::assertTrue($states['topic:' . $topicB->getId()]);
        self::assertTrue(
            $states['forum:' . $parentA->getId()],
            'Unread topics below an invisible sub forum must not bubble up to a visible ancestor.',
        );
        self::assertFalse($states['forum:' . $grandChildF->getId()]);
        self::assertFalse($states['forum:' . $parentC->getId()]);
        self::assertFalse($states['forum:' . $childD->getId()]);
        self::assertFalse($states['topic:' . $topicD->getId()]);

        $crawler = $client->request('GET', '/forum');
        self::assertSame('read', $this->markerState($crawler, 'forum', $parentA->getId()));
        self::assertSame('read', $this->markerState($crawler, 'forum', $childB->getId()));
        self::assertSame('unread', $this->markerState($crawler, 'forum', $parentC->getId()));
        self::assertSame('unread', $this->markerState($crawler, 'forum', $childD->getId()));

        $states = $this->markAsRead($client, ['type' => 'forum', 'id' => $parentC->getId()], $markers);

        self::assertTrue(
            $states['forum:' . $childD->getId()],
            'Reading a forum must also read the topics of the sub forums below it.',
        );
        self::assertTrue($states['topic:' . $topicD->getId()]);

        $crawler = $client->request('GET', '/forum/' . $parentC->getSlug());
        self::assertResponseIsSuccessful();
        self::assertSame('read', $this->markerState($crawler, 'forum', $childD->getId()));
        self::assertSame('read', $this->markerState($crawler, 'topic', $topicC->getId()));
    }

    /**
     * @param array{type: string, id: int} $subject
     * @param array<array{type: string, id: int}> $markers
     * @return array<string, bool> read state per "type:id"
     */
    private function markAsRead(KernelBrowser $client, array $subject, array $markers): array
    {
        $client->request(
            'POST',
            '/api/read-markers/mark-as-read',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode(['subject' => $subject, 'markers' => $markers], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $states = [];
        foreach ($response['markers'] as $state) {
            $states[$state['type'] . ':' . $state['id']] = $state['read'];
        }

        return $states;
    }

    private function markerState(Crawler $crawler, string $type, int $id): string
    {
        $marker = $crawler->filter(sprintf('[data-read-marker-type="%s"][data-read-marker-id="%d"]', $type, $id));
        self::assertCount(1, $marker, "No $type read marker rendered for id $id.");

        return str_contains($marker->attr('class') ?? '', 'd-none') ? 'read' : 'unread';
    }
}
