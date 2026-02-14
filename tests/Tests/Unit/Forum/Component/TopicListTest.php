<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Forum\Component;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Forum\Component\TopicList;
use Forumify\Forum\Entity\Forum;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Tests\Tests\Traits\ForumTrait;
use Tests\Tests\Traits\UserTrait;

class TopicListTest extends KernelTestCase
{
    use UserTrait;
    use ForumTrait;

    public function testSortModes(): void
    {
        $user = $this->createUser();

        $security = $this->createStub(Security::class);
        $security->method('isGranted')->willReturn(false);
        $security->method('getUser')->willReturn($user);

        $em = self::getContainer()->get(EntityManagerInterface::class);

        $forum = $this->createForum();
        $forum->setType(Forum::TYPE_SUPPORT);
        $forum->getDisplaySettings()->setOnlyShowOwnTopics(true);

        $this->createTopic($forum, author: $user);
        $this->createTopic($forum, author: $this->createUser('tester2', 'tester2@example.com'));

        $tempTl = new TopicList($security);
        $tempTl->forum = $forum;
        foreach ($tempTl->getSortModes() as ['mode' => $mode]) {
            $topicList = new TopicList($security);
            $topicList->setServices($em);
            $topicList->forum = $forum;

            $topicList->sort($mode);
            $result = $topicList->getResult();

            self::assertSame(1, $result->totalCount);
        }
    }
}
