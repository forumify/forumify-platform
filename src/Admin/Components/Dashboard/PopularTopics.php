<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

/**
 * @extends AbstractDoctrineList<Topic>
 */
#[AsLiveComponent('Forumify\\Admin\\PopularTopics', '@Forumify/admin/dashboard/components/topics.html.twig')]
class PopularTopics extends AbstractDoctrineList
{
    public function __construct(private readonly TopicRepository $topicRepository)
    {
        $this->limit = 6;
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.popular_topics';
    }

    protected function getEntityClass(): string
    {
        return Topic::class;
    }

    protected function getTotalCount(): int
    {
        return $this->limit;
    }

    protected function getQuery(): QueryBuilder
    {
        return $this->topicRepository
            ->getVisibleTopicsQuery()
            ->leftJoin('t.comments', 'c')
            ->leftJoin('c.reactions', 're')
            ->addSelect('(t.views + COUNT(re) * 5 + COUNT(c) * 25) AS HIDDEN points')
            ->having('points > 10')
            ->addGroupBy('t')
            ->addOrderBy('points', 'DESC')
        ;
    }
}
