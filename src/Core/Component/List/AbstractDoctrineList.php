<?php

declare(strict_types=1);

namespace Forumify\Core\Component\List;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractDoctrineList extends AbstractList
{
    private ?ListResult $result = null;

    abstract protected function getQueryBuilder(): QueryBuilder;

    abstract protected function getCount(): int;

    public function getResult(): ListResult
    {
        if ($this->result !== null) {
            return $this->result;
        }

        $count = $this->getCount();
        $hasPagination = $count > $this->size;
        if ($hasPagination && !$this->pageSwitched && $this->lastPageFirst) {
            $pages = array_keys(range(1, $count, $this->size));
            $lastPage = array_pop($pages) + 1;
            $this->page = $lastPage;
        }

        $data = $this->getQueryBuilder()
            ->setFirstResult(($this->page - 1) * $this->size)
            ->setMaxResults($this->size)
            ->getQuery()
            ->getResult();

        $this->result = new ListResult(
            $data,
            $this->page,
            $this->size,
            $count,
        );

        return $this->result;
    }
}
