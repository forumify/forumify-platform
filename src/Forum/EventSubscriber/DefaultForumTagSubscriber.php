<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\ForumTagRepository;

#[AsEntityListener(event: Events::prePersist, method: 'addDefaultTags', entity: Topic::class)]
class DefaultForumTagSubscriber
{
    public function __construct(private readonly ForumTagRepository $forumTagRepository)
    {
    }

    public function addDefaultTags(Topic $topic): void
    {
        $defaultTags = $this->forumTagRepository->findByForum($topic->getForum(), true);
        foreach ($defaultTags as $tag) {
            if (!$topic->tags->contains($tag)) {
                $topic->tags->add($tag);
            }
        }
    }
}
