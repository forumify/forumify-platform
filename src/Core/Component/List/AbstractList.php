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
    public int $size = 10;

    #[LiveProp]
    public bool $pageSwitched = false;

    abstract public function getResult(): ListResult;

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
