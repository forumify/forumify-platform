<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Admin;

use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Entity\Topic;
use Forumify\Testing\Factories\Forum\CommentFactory;
use Forumify\Testing\Factories\Forum\MessageFactory;
use Forumify\Testing\Factories\Forum\MessageThreadFactory;
use Forumify\Testing\Factories\Forum\TopicFactory;
use Forumify\Testing\Traits\UserTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Foundry\Test\Factories;

class UserBanControllerTest extends WebTestCase
{
    use Factories;
    use UserTrait;

    public function testBanWithoutDeletingContent(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $user = $this->createUser('banned', 'banned@example.org');
        $topic = TopicFactory::createOne(['createdBy' => $user]);

        $this->submitBanForm($client, $user, false);

        self::assertTrue($this->refresh($user)->isBanned());
        self::assertNotNull($this->find(Topic::class, $topic->getId()));
    }

    public function testDeletingContentRequiresASecondConfirmation(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $user = $this->createUser('banned', 'banned@example.org');
        $topic = TopicFactory::createOne(['createdBy' => $user]);

        $this->submitBanForm($client, $user, true);

        self::assertFalse($this->refresh($user)->isBanned());
        self::assertNotNull($this->find(Topic::class, $topic->getId()));
    }

    public function testBanAndDeleteContent(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $user = $this->createUser('banned', 'banned@example.org');
        $other = $this->createUser('other', 'other@example.org');

        $topic = TopicFactory::createOne(['createdBy' => $user]);
        $commentInOwnTopic = CommentFactory::createOne(['createdBy' => $user, 'topic' => $topic]);

        $otherTopic = TopicFactory::createOne(['createdBy' => $other]);
        $commentInOtherTopic = CommentFactory::createOne(['createdBy' => $user, 'topic' => $otherTopic]);
        $otherComment = CommentFactory::createOne(['createdBy' => $other, 'topic' => $otherTopic]);

        $thread = MessageThreadFactory::createOne(['createdBy' => $user]);
        $otherThread = MessageThreadFactory::createOne(['createdBy' => $other]);
        $message = MessageFactory::createOne(['createdBy' => $user, 'thread' => $otherThread]);
        $otherMessage = MessageFactory::createOne(['createdBy' => $other, 'thread' => $otherThread]);

        $crawler = $this->submitBanForm($client, $user, true);
        $client->click($crawler->selectLink('Ban and delete all content')->link());
        self::assertResponseIsSuccessful();

        self::assertTrue($this->refresh($user)->isBanned());
        self::assertNull($this->find(Topic::class, $topic->getId()));
        self::assertNull($this->find(Comment::class, $commentInOwnTopic->getId()));
        self::assertNull($this->find(Comment::class, $commentInOtherTopic->getId()));
        self::assertNull($this->find(MessageThread::class, $thread->getId()));
        self::assertNull($this->find(Message::class, $message->getId()));

        self::assertNotNull($this->find(Topic::class, $otherTopic->getId()));
        self::assertNotNull($this->find(Comment::class, $otherComment->getId()));
        self::assertNotNull($this->find(MessageThread::class, $otherThread->getId()));
        self::assertNotNull($this->find(Message::class, $otherMessage->getId()));
    }

    public function testAlreadyBannedUserCannotBeBannedAgain(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $user = $this->createUser('banned', 'banned@example.org');
        $this->submitBanForm($client, $user, false);

        $topic = TopicFactory::createOne(['createdBy' => $user]);
        $client->request('GET', "/admin/users/{$user->getId()}/ban?deleteContent=1&confirmed=1");
        self::assertResponseIsSuccessful();

        self::assertNotNull($this->find(Topic::class, $topic->getId()));
    }

    private function submitBanForm(KernelBrowser $client, User $user, bool $deleteContent): Crawler
    {
        $crawler = $client->request('GET', "/admin/users/{$user->getId()}/ban");
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ban')->form();
        if ($deleteContent) {
            $form['form[deleteContent]']->tick();
        }

        $crawler = $client->submit($form);
        self::assertResponseIsSuccessful();

        return $crawler;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T|null
     */
    private function find(string $class, int $id): ?object
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->clear();

        return $em->find($class, $id);
    }

    private function refresh(User $user): User
    {
        $refreshed = $this->find(User::class, $user->getId());
        self::assertNotNull($refreshed);

        return $refreshed;
    }
}
