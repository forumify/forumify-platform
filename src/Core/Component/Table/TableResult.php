<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Table;

class TableResult
{
    /**
     * @param array<array<string, mixed>> $rows
     * @param int $totalCount
     */
    public function __construct(
        public readonly array $rows,
        public readonly int $totalCount,
    ) {
    }
}
