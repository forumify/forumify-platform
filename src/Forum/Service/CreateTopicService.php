<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Event\TopicCreatedEvent;
use Forumify\Forum\Form\NewComment;
use Forumify\Forum\Form\NewTopic;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateTopicService
{
    public function __construct(
        private readonly TopicRepository $topicRepository,
        private readonly CreateCommentService $commentService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createTopic(Forum $forum, NewTopic $newTopic): Topic
    {
        $topic = new Topic();
        $topic->setTitle($newTopic->getTitle());
        $topic->setForum($forum);
        $this->topicRepository->save($topic);

        $newComment = new NewComment();
        $newComment->setContent($newTopic->getContent());
        $comment = $this->commentService->createComment($topic, $newComment);
        $topic->setComments([$comment]);

        $this->eventDispatcher->dispatch(new TopicCreatedEvent($topic));
        return $topic;
    }
}
