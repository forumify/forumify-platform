<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\AbstractList;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(name: 'TopicList', template: '@Forumify/frontend/components/topic_list.html.twig')]
class TopicList extends AbstractList
{
    #[LiveProp]
    public Forum $forum;

    public function __construct(private readonly TopicRepository $topicRepository)
    {
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->topicRepository
            ->createQueryBuilder('t')
            ->where('t.forum = :forum')
            ->join('t.lastComment', 'lc')
            ->orderBy('lc.createdAt', 'DESC')
            ->setParameter('forum', $this->forum);
    }

    protected function getCount(): int
    {
        return $this->topicRepository->count(['forum' => $this->forum]);
    }
}
