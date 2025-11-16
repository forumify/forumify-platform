<?php

declare(strict_types=1);

namespace Forumify\Core\Component\List;

class ListResult
{
    /**
     * @param array<mixed> $rows
     */
    public function __construct(
        public readonly array $rows,
        public readonly int $totalCount,
    ) {
    }
}
