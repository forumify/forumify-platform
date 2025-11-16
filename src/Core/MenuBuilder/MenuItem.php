<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder;

class MenuItem
{
    /**
     * @param string $label
     * @param string $location
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly string $label,
        public readonly string $location,
        public readonly array $options = [],
    ) {
    }

    public function getPermission(): ?string
    {
        return $this->options['permission'] ?? null;
    }
}
