<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Repository\NotificationRepository;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Notification\MentionNotificationType;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Factories\Forum\CommentFactory;
use Forumify\Testing\Factories\Forum\ForumFactory;
use Forumify\Testing\Factories\Forum\MessageFactory;
use Forumify\Testing\Factories\Forum\MessageThreadFactory;
use Forumify\Testing\Factories\Forum\TopicFactory;
use Forumify\Testing\Traits\ACLTrait;
use Forumify\Testing\Traits\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class QuoteControllerTest extends WebTestCase
{
    use UserTrait;
    use ACLTrait;
    use Factories;

    public function testQuoteComment(): void
    {
        $client = static::createClient();

        $forum = ForumFactory::createOne();
        $this->createACL(Forum::class, $forum->getId(), 'view');

        $author = UserFactory::createOne(['username' => 'author', 'displayName' => 'The Author']);
        $comment = CommentFactory::createOne([
            'topic' => TopicFactory::createOne(['forum' => $forum]),
            'createdBy' => $author,
            'content' => '<p>Hello <strong>world</strong></p><ul><li>one</li></ul>',
        ]);

        $client->loginUser($this->createUser());
        $client->request('GET', "/comment/{$comment->getId()}/quote");
        self::assertResponseIsSuccessful();

        $quote = (string)$client->getResponse()->getContent();
        self::assertStringContainsString('<blockquote class="forumify-quote">', $quote);
        self::assertStringContainsString('class="mention"', $quote);
        self::assertStringContainsString('data-id="' . $author->getId() . '"', $quote);
        self::assertStringContainsString('The Author', $quote);
        self::assertStringContainsString('<p>Hello <strong>world</strong></p><ul><li>one</li></ul>', $quote);
    }

    public function testQuoteCommentRemovesNestedQuotes(): void
    {
        $client = static::createClient();

        $forum = ForumFactory::createOne();
        $this->createACL(Forum::class, $forum->getId(), 'view');

        $comment = CommentFactory::createOne([
            'topic' => TopicFactory::createOne(['forum' => $forum]),
            'content' => '<blockquote class="forumify-quote">'
                . '<div class="forumify-quote-header">someone wrote</div>'
                . '<div class="forumify-quote-content"><p>quoted earlier</p></div>'
                . '</blockquote><p>my own words</p>',
        ]);

        $client->loginUser($this->createUser());
        $client->request('GET', "/comment/{$comment->getId()}/quote");
        self::assertResponseIsSuccessful();

        $quote = (string)$client->getResponse()->getContent();
        self::assertStringContainsString('<p>my own words</p>', $quote);
        self::assertStringNotContainsString('quoted earlier', $quote);
    }

    public function testQuoteCommentWithoutAccess(): void
    {
        $client = static::createClient();

        $comment = CommentFactory::createOne([
            'topic' => TopicFactory::createOne(['forum' => ForumFactory::createOne()]),
        ]);

        $client->loginUser($this->createUser());
        $client->request('GET', "/comment/{$comment->getId()}/quote");

        self::assertResponseStatusCodeSame(403);
    }

    public function testQuoteMessage(): void
    {
        $client = static::createClient();

        $user = $this->createUser();
        $author = UserFactory::createOne(['username' => 'author', 'displayName' => 'The Author']);
        $thread = MessageThreadFactory::createOne(['participants' => [$user, $author]]);
        $message = MessageFactory::createOne([
            'thread' => $thread,
            'createdBy' => $author,
            'content' => '<p>Hello from a message</p>',
        ]);

        $client->loginUser($user);
        $client->request('GET', "/messenger/message/{$message->getId()}/quote");
        self::assertResponseIsSuccessful();

        $quote = (string)$client->getResponse()->getContent();
        self::assertStringContainsString('data-id="' . $author->getId() . '"', $quote);
        self::assertStringContainsString('<p>Hello from a message</p>', $quote);
    }

    public function testQuoteMessageWithoutAccess(): void
    {
        $client = static::createClient();

        $message = MessageFactory::createOne([
            'thread' => MessageThreadFactory::createOne(['participants' => [UserFactory::createOne()]]),
        ]);

        $client->loginUser($this->createUser());
        $client->request('GET', "/messenger/message/{$message->getId()}/quote");

        self::assertResponseStatusCodeSame(403);
    }

    public function testQuotedAuthorIsMentioned(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = ForumFactory::createOne();
        $this->createACL(Forum::class, $forum->getId(), 'view');
        $this->createACL(Forum::class, $forum->getId(), 'create_comment');

        $author = UserFactory::createOne(['username' => 'author', 'displayName' => 'The Author']);
        $topic = TopicFactory::createOne(['forum' => $forum]);
        $comment = CommentFactory::createOne([
            'topic' => $topic,
            'createdBy' => $author,
            'content' => '<p>Quote me</p>',
        ]);

        $client->loginUser($this->createUser());
        $client->request('GET', "/comment/{$comment->getId()}/quote");
        $quote = (string)$client->getResponse()->getContent();

        $client->request('GET', "/topic/{$topic->getSlug()}");
        $client->submitForm('Post comment', [
            'new_comment[content]' => $quote . '<p>I agree</p>',
        ]);
        self::assertResponseIsSuccessful();

        $notifications = self::getContainer()
            ->get(NotificationRepository::class)
            ->findBy(['type' => MentionNotificationType::TYPE, 'recipient' => $author->getId()]);

        self::assertCount(1, $notifications);
        self::assertInstanceOf(Notification::class, $notifications[0]);
    }
}
