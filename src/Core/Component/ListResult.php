<?php

declare(strict_types=1);

namespace Forumify\Core\Component;

class ListResult
{
    public function __construct(
        private readonly array $data = [],
        private readonly int $page = 0,
        private readonly int $size = 0,
        private readonly int $count = 0,
    ) {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
