<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use DateInterval;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\Admin\\LatestTopics', '@Forumify/admin/dashboard/components/topics.html.twig')]
class LatestTopics extends AbstractDoctrineList
{
    public function __construct(private readonly TopicRepository $topicRepository)
    {
        $this->size = 5;
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.latest_topics';
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->getQuery();
    }

    protected function getCount(): int
    {
        return $this->getQuery()
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getQuery(): QueryBuilder
    {
        $min = (new DateTime())->sub(new DateInterval('P1M'));
        $qb = $this->topicRepository
            ->getVisibleTopicsQuery()
            ->andWhere('t.createdAt > :min')
            ->setParameter('min', $min)
            ->orderBy('t.createdAt', 'desc')
        ;

        return $qb;
    }
}
