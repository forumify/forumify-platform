<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Table;

class TableResult
{
    public function __construct(
        public readonly array $rows,
        public readonly int $totalCount,
    ) {
    }
}
