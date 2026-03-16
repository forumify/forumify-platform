<?php

declare(strict_types=1);

namespace Forumify\Core\Component\List;

use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

abstract class AbstractList
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp]
    public bool $lastPageFirst = false;

    #[LiveProp(writable: true)]
    public int $limit = 10;

    #[LiveProp]
    public bool $infiniteScroll = false;

    #[LiveProp]
    public bool $disablePagination = false;

    private ?ListResult $result = null;

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    #[LiveAction]
    public function setLimit(#[LiveArg] int $limit): void
    {
        $this->limit = $limit;
    }

    public function getResult(): ListResult
    {
        if ($this->result !== null) {
            return $this->result;
        }

        $totalCount = $this->disablePagination
            ? $this->limit
            : $this->getTotalCount();

        $this->result = new ListResult(
            $this->getData(),
            $totalCount,
        );
        return $this->result;
    }

    /**
     * @return array<mixed>
     */
    abstract protected function getData(): array;

    abstract protected function getTotalCount(): int;
}
