<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\Admin\\PopularTopics', '@Forumify/admin/dashboard/components/topics.html.twig')]
class PopularTopics extends AbstractDoctrineList
{
    public function __construct(private readonly TopicRepository $topicRepository)
    {
        $this->size = 6;
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.popular_topics';
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->getQuery();
    }

    protected function getCount(): int
    {
        return 0;
    }

    private function getQuery(): QueryBuilder
    {
        $qb = $this->topicRepository
            ->getVisibleTopicsQuery()
            ->leftJoin('t.comments', 'c')
            ->leftJoin('c.reactions', 're')
            ->addSelect('(t.views + COUNT(re) * 5 + COUNT(c) * 25) AS HIDDEN points')
            ->having('points > 10')
            ->addGroupBy('t')
            ->addOrderBy('points', 'DESC')
        ;

        return $qb;
    }
}
