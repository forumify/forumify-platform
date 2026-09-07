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

class UserDeleteControllerTest extends WebTestCase
{
    use Factories;
    use UserTrait;

    public function testDeleteAnonymizesContentByDefault(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $user = $this->createUser('deleted', 'deleted@example.org');

        $topic = TopicFactory::createOne(['createdBy' => $user]);
        $comment = CommentFactory::createOne(['createdBy' => $user, 'topic' => $topic]);

        $this->submitDeleteForm($client, $user, false);

        self::assertNull($this->find(User::class, $user->getId()));
        self::assertNotNull($this->find(Topic::class, $topic->getId()));
        self::assertNull($this->find(Topic::class, $topic->getId())?->getCreatedBy());
        self::assertNull($this->find(Comment::class, $comment->getId())?->getCreatedBy());
    }

    public function testDeletingContentRequiresASecondConfirmation(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $user = $this->createUser('deleted', 'deleted@example.org');
        $topic = TopicFactory::createOne(['createdBy' => $user]);

        $this->submitDeleteForm($client, $user, true);

        self::assertNotNull($this->find(User::class, $user->getId()));
        self::assertNotNull($this->find(Topic::class, $topic->getId()));
    }

    public function testDeleteUserAndContent(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $user = $this->createUser('deleted', 'deleted@example.org');
        $other = $this->createUser('other', 'other@example.org');

        $topic = TopicFactory::createOne(['createdBy' => $user]);
        $commentInOwnTopic = CommentFactory::createOne(['createdBy' => $user, 'topic' => $topic]);

        $otherTopic = TopicFactory::createOne(['createdBy' => $other]);
        $commentInOtherTopic = CommentFactory::createOne(['createdBy' => $user, 'topic' => $otherTopic]);
        $otherComment = CommentFactory::createOne(['createdBy' => $other, 'topic' => $otherTopic]);

        $thread = MessageThreadFactory::createOne(['createdBy' => $user]);
        $otherThread = MessageThreadFactory::createOne(['createdBy' => $other]);
        $message = MessageFactory::createOne(['createdBy' => $user, 'thread' => $otherThread]);

        $crawler = $this->submitDeleteForm($client, $user, true);
        $client->click($crawler->selectLink('Delete user and all content')->link());
        self::assertResponseIsSuccessful();

        self::assertNull($this->find(User::class, $user->getId()));
        self::assertNull($this->find(Topic::class, $topic->getId()));
        self::assertNull($this->find(Comment::class, $commentInOwnTopic->getId()));
        self::assertNull($this->find(Comment::class, $commentInOtherTopic->getId()));
        self::assertNull($this->find(MessageThread::class, $thread->getId()));
        self::assertNull($this->find(Message::class, $message->getId()));

        self::assertNotNull($this->find(Topic::class, $otherTopic->getId()));
        self::assertNotNull($this->find(Comment::class, $otherComment->getId()));
        self::assertNotNull($this->find(MessageThread::class, $otherThread->getId()));
    }

    private function submitDeleteForm(KernelBrowser $client, User $user, bool $deleteContent): Crawler
    {
        $crawler = $client->request('GET', "/admin/users/{$user->getId()}/delete");
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Delete')->form();
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
}
