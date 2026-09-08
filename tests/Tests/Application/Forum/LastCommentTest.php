<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use DateTime;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\TopicRepository;
use Forumify\Testing\Factories\Core\ACLFactory;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Factories\Forum\CommentFactory;
use Forumify\Testing\Factories\Forum\ForumFactory;
use Forumify\Testing\Factories\Forum\TopicFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Foundry\Test\Factories;

class LastCommentTest extends WebTestCase
{
    use Factories;

    public function testLastCommentRollsUpTheTreeAndRefreshesPerForum(): void
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

        CommentFactory::createOne(['topic' => $topicB, 'createdAt' => new DateTime('2024-01-01 10:00:00')]);
        $newestInB = CommentFactory::createOne(['topic' => $topicB, 'createdAt' => new DateTime('2024-01-02 10:00:00')]);
        $ownCommentInC = CommentFactory::createOne(['topic' => $topicC, 'createdAt' => new DateTime('2024-01-03 10:00:00')]);
        $commentInD = CommentFactory::createOne(['topic' => $topicD, 'createdAt' => new DateTime('2024-01-04 10:00:00')]);
        CommentFactory::createOne(['topic' => $topicE, 'createdAt' => new DateTime('2024-06-01 10:00:00')]);
        CommentFactory::createOne(['topic' => $topicF, 'createdAt' => new DateTime('2024-06-02 10:00:00')]);

        $client->loginUser(UserFactory::createOne());

        $crawler = $client->request('GET', '/forum');
        self::assertResponseIsSuccessful();

        self::assertSame(
            $newestInB->getId(),
            $this->lastCommentId($crawler, 'Parent A'),
            'A has no topics of its own, so its last comment comes from sub forum B. '
                . 'The newer comments below the invisible sub forum must not surface.',
        );
        self::assertSame(
            $commentInD->getId(),
            $this->lastCommentId($crawler, 'Parent C'),
            'D has a newer comment than C itself, so it wins the roll up.',
        );

        $newestInD = CommentFactory::createOne([
            'topic' => $this->findTopic($topicD->getId()),
            'createdAt' => new DateTime('2024-01-05 10:00:00'),
        ]);

        $crawler = $client->request('GET', '/forum');
        self::assertSame(
            $newestInD->getId(),
            $this->lastCommentId($crawler, 'Parent C'),
            'A new comment must invalidate the cache of the forum it was posted in.',
        );
        self::assertSame(
            $newestInB->getId(),
            $this->lastCommentId($crawler, 'Parent A'),
            'The unrelated tree keeps its last comment.',
        );

        $crawler = $client->request('GET', '/forum/' . $parentC->getSlug());
        self::assertResponseIsSuccessful();
        self::assertSame($newestInD->getId(), $this->lastCommentId($crawler, 'Child D'));

        $this->hideTopic($topicD->getId());

        $crawler = $client->request('GET', '/forum');
        self::assertSame(
            $ownCommentInC->getId(),
            $this->lastCommentId($crawler, 'Parent C'),
            'Hiding a topic must invalidate the forum it lives in and fall back to the next comment.',
        );

        $crawler = $client->request('GET', '/forum/' . $parentC->getSlug());
        self::assertNull(
            $this->lastCommentId($crawler, 'Child D'),
            'D only had hidden topics left, so it has no visible last comment.',
        );
    }

    private function findTopic(int $topicId): object
    {
        return self::getContainer()->get(TopicRepository::class)->find($topicId);
    }

    private function hideTopic(int $topicId): void
    {
        $repository = self::getContainer()->get(TopicRepository::class);

        $topic = $repository->find($topicId);
        $topic->setHidden(true);
        $repository->save($topic);
    }

    private function lastCommentId(Crawler $crawler, string $forumTitle): ?int
    {
        $rows = $crawler->filter('li')->reduce(static function (Crawler $row) use ($forumTitle) {
            $title = $row->filter('h3 a');

            return $title->count() > 0 && trim($title->first()->text()) === $forumTitle;
        });
        self::assertCount(1, $rows, "No forum row rendered for $forumTitle.");

        $link = $rows->first()->filter('a[href*="comment="]');
        if ($link->count() === 0) {
            return null;
        }

        preg_match('/comment=(\d+)/', $link->attr('href'), $matches);

        return (int)$matches[1];
    }
}
