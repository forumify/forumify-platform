<?php

declare(strict_types=1);

namespace Tests\Tests\Traits;

use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumDisplaySettings;
use Forumify\Forum\Form\TopicData;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Service\CreateTopicService;

trait ForumTrait
{
    use RequiresContainerTrait;

    public function createForum(
        string $title = 'Test Forum',
        ?Forum $parent = null,
        ?ForumDisplaySettings $displaySettings = null,
    ): Forum {
        $forum = new Forum();
        $forum->setTitle($title);
        $forum->setParent($parent);
        if ($displaySettings) {
            $forum->setDisplaySettings($displaySettings);
        }

        self::getContainer()->get(ForumRepository::class)->save($forum);
        return $forum;
    }

    public function createTopic(
        ?Forum $forum = null,
        string $title = 'Test Topic',
        ?User $author = null,
        ?string $content = 'Test Comment',
    ): void {
        $forum ??= $this->createForum();

        $topicData = new TopicData();
        $topicData->setTitle($title);
        $topicData->setAuthor($author);
        $topicData->setContent($content);

        self::getContainer()->get(CreateTopicService::class)->createTopic($forum, $topicData);
    }
}
