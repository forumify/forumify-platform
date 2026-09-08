<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Forumify\Core\Repository\NotificationRepository;
use Forumify\Forum\Entity\Subscription;
use Forumify\Forum\Notification\MessageReplyNotificationType;
use Forumify\Forum\Repository\MessageThreadRepository;
use Forumify\Forum\Repository\SubscriptionRepository;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Traits\UserTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\Factories;

class MessengerControllerTest extends WebTestCase
{
    use UserTrait;
    use Factories;

    public function testSendMessage(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $recipient = UserFactory::createOne();

        $c = $client->request('GET', '/messenger');
        self::assertResponseIsSuccessful();

        $c = $c->filter('a[href="/messenger/create"]')->link();
        $client->click($c);
        self::assertResponseIsSuccessful();

        $client->submitForm('Send message', [
            'new_message_thread[title]' => 'test',
            'new_message_thread[participants]' => [$recipient->getId()],
            'new_message_thread[message]' => '<p>This is a test message</p>',
        ]);
        self::assertResponseIsSuccessful();
        self::assertAnySelectorTextContains('p', 'This is a test message');
    }

    public function testCreateThreadSubscribesAllParticipants(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $sender = $this->createAdmin();
        $client->loginUser($sender);
        $recipient = UserFactory::createOne();

        $this->createThread($client, $recipient->getId());

        $thread = $this->getThreadRepository()->findOneBy(['title' => 'test']);
        self::assertNotNull($thread);

        self::assertNotNull($this->findSubscription($sender->getId(), $thread->getId()));
        self::assertNotNull($this->findSubscription($recipient->getId(), $thread->getId()));
    }

    public function testUnsubscribedParticipantIsNotNotified(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $sender = $this->createAdmin();
        $client->loginUser($sender);
        $recipient = UserFactory::createOne();

        $this->createThread($client, $recipient->getId());
        $thread = $this->getThreadRepository()->findOneBy(['title' => 'test']);
        self::assertNotNull($thread);

        $subscription = $this->findSubscription($recipient->getId(), $thread->getId());
        self::assertNotNull($subscription);
        $this->getSubscriptionRepository()->remove($subscription);

        $notificationsBefore = $this->countMessageReplyNotifications($recipient->getId());
        $client->submitForm('Reply', ['message_reply[content]' => '<p>Second message</p>']);
        self::assertResponseIsSuccessful();

        self::assertSame($notificationsBefore, $this->countMessageReplyNotifications($recipient->getId()));
    }

    public function testSubscribedParticipantIsNotified(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $sender = $this->createAdmin();
        $client->loginUser($sender);
        $recipient = UserFactory::createOne();

        $this->createThread($client, $recipient->getId());
        $thread = $this->getThreadRepository()->findOneBy(['title' => 'test']);
        self::assertNotNull($thread);

        $notificationsBefore = $this->countMessageReplyNotifications($recipient->getId());
        $client->submitForm('Reply', ['message_reply[content]' => '<p>Second message</p>']);
        self::assertResponseIsSuccessful();

        self::assertSame($notificationsBefore + 1, $this->countMessageReplyNotifications($recipient->getId()));
    }

    public function testAddParticipantSubscribesNewParticipant(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $recipient = UserFactory::createOne();
        $newParticipant = UserFactory::createOne();

        $this->createThread($client, $recipient->getId());
        $thread = $this->getThreadRepository()->findOneBy(['title' => 'test']);
        self::assertNotNull($thread);

        $client->request('GET', "/messenger/{$thread->getId()}/add-participant");
        $client->submitForm('Save', ['form[participants]' => [$newParticipant->getId()]]);
        self::assertResponseIsSuccessful();

        self::assertNotNull($this->findSubscription($newParticipant->getId(), $thread->getId()));
    }

    private function createThread(KernelBrowser $client, int $recipientId): void
    {
        $client->request('GET', '/messenger/create');
        $client->submitForm('Send message', [
            'new_message_thread[title]' => 'test',
            'new_message_thread[participants]' => [$recipientId],
            'new_message_thread[message]' => '<p>This is a test message</p>',
        ]);
    }

    private function findSubscription(int $userId, int $threadId): ?Subscription
    {
        return $this->getSubscriptionRepository()->findOneBy([
            'user' => $userId,
            'type' => MessageReplyNotificationType::TYPE,
            'subjectId' => $threadId,
        ]);
    }

    private function countMessageReplyNotifications(int $userId): int
    {
        return self::getContainer()
            ->get(NotificationRepository::class)
            ->count(['recipient' => $userId, 'type' => MessageReplyNotificationType::TYPE]);
    }

    private function getSubscriptionRepository(): SubscriptionRepository
    {
        return self::getContainer()->get(SubscriptionRepository::class);
    }

    private function getThreadRepository(): MessageThreadRepository
    {
        return self::getContainer()->get(MessageThreadRepository::class);
    }
}
