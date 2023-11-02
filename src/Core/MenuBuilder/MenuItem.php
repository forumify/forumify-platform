<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder;

class MenuItem
{
    public function __construct(
        public readonly string $label,
        public readonly string $location,
        public readonly array $options = [],
    ) {
    }
}
