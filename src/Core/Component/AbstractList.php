<?php

declare(strict_types=1);

namespace Forumify\Core\Component;

use Doctrine\ORM\QueryBuilder;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

abstract class AbstractList
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public int $size = 10;

    #[LiveProp]
    public bool $lastPageFirst = false;

    #[LiveProp]
    public bool $pageSwitched = false;

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

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->pageSwitched = true;
        $this->page = $page;
    }

    #[LiveAction]
    public function setSize(#[LiveArg] int $size): void
    {
        $this->size = $size;
    }
}
