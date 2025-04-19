<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Forum\Repository;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumDisplaySettings;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Tests\Traits\ACLTrait;
use Tests\Tests\Traits\ForumTrait;
use Tests\Tests\Traits\UserTrait;

class CommentRepositoryTest extends KernelTestCase
{
    use UserTrait;
    use ForumTrait;
    use ACLTrait;

    public function testGetUserLastComments(): void
    {
        $user = $this->createUser();

        $forum1 = $this->createForum();
        $this->createACL(Forum::class, $forum1->getId(), 'view', [$this->getGuestRole()]);
        $this->createTopic($forum1, author: $user, content: 'Visible Comment');

        $displaySettings = new ForumDisplaySettings();
        $displaySettings->setOnlyShowOwnTopics(true);
        $forum2 = $this->createForum(displaySettings: $displaySettings);
        $this->createACL(Forum::class, $forum2->getId(), 'view', [$this->getGuestRole()]);
        $this->createTopic($forum2, author: $user, content: 'Invisible Comment');

        /** @var CommentRepository $repository */
        $repository = self::getContainer()->get(CommentRepository::class);
        $comments = $repository->getUserLastComments($user);

        self::assertCount(1, $comments);
        self::assertEquals('Visible Comment', $comments[0]->getContent());
    }
}
