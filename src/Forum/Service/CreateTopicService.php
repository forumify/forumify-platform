<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Service\MediaService;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Event\TopicCreatedEvent;
use Forumify\Forum\Form\NewComment;
use Forumify\Forum\Form\TopicData;
use Forumify\Forum\Repository\ForumTagRepository;
use Forumify\Forum\Repository\TopicRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateTopicService
{
    public function __construct(
        private readonly TopicRepository $topicRepository,
        private readonly CreateCommentService $commentService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FilesystemOperator $mediaStorage,
        private readonly MediaService $mediaService,
        private readonly ForumTagRepository $forumTagRepository,
    ) {
    }

    public function createTopic(Forum $forum, TopicData $newTopic): Topic
    {
        $topic = new Topic();
        $topic->setTitle($newTopic->getTitle());
        $topic->setCreatedBy($newTopic->getAuthor());
        $topic->setForum($forum);
        if ($newTopic->getImage() !== null) {
            $image = $this->mediaService->saveToFilesystem($this->mediaStorage, $newTopic->getImage());
            $topic->setImage($image);
        }

        $topic->tags = $newTopic->getTags();
        $defaultTags = $this->forumTagRepository->findByForum($forum, true);
        foreach ($defaultTags as $defaultTag) {
            if (!$topic->tags->contains($defaultTag)) {
                $topic->tags->add($defaultTag);
            }
        }

        $this->topicRepository->save($topic);

        $newComment = new NewComment();
        if ($newTopic->getContent() !== null) {
            $newComment->setContent($newTopic->getContent());
        }

        $newComment->setAuthor($newTopic->getAuthor());
        $comment = $this->commentService->createComment($topic, $newComment);
        $topic->setFirstComment($comment);
        $topic->setComments([$comment]);

        $this->eventDispatcher->dispatch(new TopicCreatedEvent($topic));
        return $topic;
    }
}
