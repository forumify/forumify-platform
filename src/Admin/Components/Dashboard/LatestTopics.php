<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use DateInterval;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\Admin\\LatestTopics', '@Forumify/admin/dashboard/components/topics.html.twig')]
class LatestTopics extends AbstractDoctrineList
{
    public function __construct(private readonly TopicRepository $topicRepository)
    {
        $this->limit = 5;
    }

    protected function getEntityClass(): string
    {
        return Topic::class;
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.latest_topics';
    }

    protected function getTotalCount(): int
    {
        return (int) $this->getQuery()
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    protected function getQuery(): QueryBuilder
    {
        $min = (new DateTime())->sub(new DateInterval('P1M'));
        return $this->topicRepository
            ->getVisibleTopicsQuery()
            ->andWhere('t.createdAt > :min')
            ->setParameter('min', $min)
            ->orderBy('t.createdAt', 'desc')
        ;
    }
}
