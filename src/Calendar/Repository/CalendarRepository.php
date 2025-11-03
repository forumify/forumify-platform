<?php

declare(strict_types=1);

namespace Forumify\Calendar\Repository;

use Doctrine\ORM\QueryBuilder;
use Forumify\Calendar\Entity\Calendar;
use Forumify\Core\Repository\AbstractRepository;

/**
 * @extends AbstractRepository<Calendar>
 */
class CalendarRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Calendar::class;
    }

    /**
     * @return array<Calendar>
     */
    public function findAllVisibleCalendars(): array
    {
        $qb = $this->createQueryBuilder('c');
        $this->addACLToQuery($qb, 'view', Calendar::class, 'c');

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function countManageableCalendars(): int
    {
        $qb = $this->getManageableCalendarsQuery();

        try {
            return (int) $qb
                ->select('COUNT(c)')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        } catch (\Exception) {
            return 0;
        }
    }

    public function getManageableCalendarsQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $this->addACLToQuery($qb, 'manage_events', Calendar::class, 'c');

        return $qb;
    }
}
