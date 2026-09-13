<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Forum\Repository;

use Forumify\Forum\Repository\TopicRepository;
use Forumify\Testing\Factories\Forum\ForumFactory;
use Forumify\Testing\Factories\Forum\TopicFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class TopicRepositoryTest extends KernelTestCase
{
    use Factories;

    public function testFindImagesByForumFlattensAndSkipsTopicsWithoutImages(): void
    {
        $forum = ForumFactory::createOne();
        $otherForum = ForumFactory::createOne();

        TopicFactory::createOne(['forum' => $forum, 'images' => ['first.png', 'second.png']]);
        TopicFactory::createOne(['forum' => $forum, 'images' => ['third.png']]);
        TopicFactory::createOne(['forum' => $forum]);
        TopicFactory::createOne(['forum' => $otherForum, 'images' => ['other.png']]);

        /** @var TopicRepository $repository */
        $repository = self::getContainer()->get(TopicRepository::class);
        $images = $repository->findImagesByForum($forum->getId());

        self::assertCount(3, $images);
        self::assertContains('first.png', $images);
        self::assertContains('second.png', $images);
        self::assertContains('third.png', $images);
        self::assertNotContains('other.png', $images);
    }

    public function testFindImagesByForumReturnsEmptyArrayWhenNothingHasImages(): void
    {
        $forum = ForumFactory::createOne();
        TopicFactory::createOne(['forum' => $forum]);

        /** @var TopicRepository $repository */
        $repository = self::getContainer()->get(TopicRepository::class);

        self::assertSame([], $repository->findImagesByForum($forum->getId()));
    }
}
