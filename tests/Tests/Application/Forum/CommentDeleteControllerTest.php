<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\CommentRepository;
use Forumify\Forum\Repository\TopicRepository;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Factories\Forum\CommentFactory;
use Forumify\Testing\Factories\Forum\ForumFactory;
use Forumify\Testing\Factories\Forum\TopicFactory;
use Forumify\Testing\Traits\ACLTrait;
use Forumify\Testing\Traits\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class CommentDeleteControllerTest extends WebTestCase
{
    use ACLTrait;
    use Factories;
    use UserTrait;

    public function testDeletingTheFirstCommentRedirectsToTopicDelete(): void
    {
        $client = static::createClient();

        $author = UserFactory::createOne();
        [$topic, $firstComment] = $this->createTopicWithComments($author);
        $commentId = $firstComment->getId();

        $client->loginUser($author);
        $client->request('GET', "/comment/$commentId/delete");
        self::assertResponseRedirects("/topic/{$topic->getSlug()}/delete");

        self::assertNotNull($this->findComment($commentId));
        self::assertNotNull($this->findTopic($topic->getId())?->getFirstComment());
    }

    public function testSuperAdminCanNotDeleteTheFirstCommentEither(): void
    {
        $client = static::createClient();

        [$topic, $firstComment] = $this->createTopicWithComments(UserFactory::createOne());
        $commentId = $firstComment->getId();

        $client->loginUser($this->createAdmin());
        $client->request('GET', "/comment/$commentId/delete");
        self::assertResponseRedirects("/topic/{$topic->getSlug()}/delete");

        self::assertNotNull($this->findComment($commentId));
    }

    public function testOtherCommentsCanStillBeDeleted(): void
    {
        $client = static::createClient();

        $author = UserFactory::createOne();
        [$topic, $firstComment, $reply] = $this->createTopicWithComments($author);
        $replyId = $reply->getId();

        $client->loginUser($author);
        $client->request('GET', "/comment/$replyId/delete");
        self::assertResponseRedirects("/topic/{$topic->getSlug()}");

        self::assertNull($this->findComment($replyId));
        self::assertNotNull($this->findComment($firstComment->getId()));
    }

    /**
     * @return array{Topic, Comment, Comment}
     */
    private function createTopicWithComments(object $author): array
    {
        $forum = ForumFactory::createOne();
        $this->createACL(Forum::class, $forum->getId(), 'view');

        $topic = TopicFactory::createOne(['forum' => $forum, 'createdBy' => $author]);
        $firstComment = CommentFactory::createOne(['topic' => $topic, 'createdBy' => $author]);
        $reply = CommentFactory::createOne(['topic' => $topic, 'createdBy' => $author]);

        $topic->setFirstComment($firstComment);
        self::getContainer()->get(TopicRepository::class)->save($topic);

        self::getContainer()->get('doctrine')->getManager()->clear();

        return [$topic, $firstComment, $reply];
    }

    private function findComment(int $id): ?Comment
    {
        return self::getContainer()->get(CommentRepository::class)->find($id);
    }

    private function findTopic(int $id): ?Topic
    {
        return self::getContainer()->get(TopicRepository::class)->find($id);
    }
}
