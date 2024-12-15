<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Graph;

class GraphDataPoint
{
    public function __construct(
        public string $label,
        public int|float $value,
    ) {
    }
}
