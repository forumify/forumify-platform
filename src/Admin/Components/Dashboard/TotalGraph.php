<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use Forumify\Core\Component\Graph\AbstractGraph;
use Forumify\Core\Component\Graph\GraphDataPoint;
use Forumify\Core\Repository\AbstractRepository;

abstract class TotalGraph extends AbstractGraph
{
    public function __construct(
        private readonly AbstractRepository $repository,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE_LINE;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        $qb = $this->repository->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.createdAt IS NULL OR u.createdAt <= :date');

        $date = new \DateTime();
        $date->setTime(23, 59, 59);

        $points = [];
        for ($i = 0; $i < 12; $i++) {
            $count = $qb->setParameter('date', $date)
                ->getQuery()
                ->getSingleScalarResult();

            $points[] = new GraphDataPoint(
                $date->format('M'),
                $count,
            );

            $date->modify("last day of previous month");
        }

        return array_reverse($points);
    }

    public function getTotal(): int
    {
        return $this->repository->count([]);
    }

    abstract public function getTitle(): string;
    abstract public function getIcon(): string;

    public function getGraphHeight(): int
    {
        return 120;
    }
}
