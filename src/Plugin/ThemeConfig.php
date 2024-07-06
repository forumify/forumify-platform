<?php

declare(strict_types=1);

namespace Forumify\Plugin;

class ThemeConfig
{
    /**
     * @param array<ThemeVar> $vars
     */
    public function __construct(
        public readonly bool $hasDarkVariant = false,
        public readonly array $vars = [],
    ) {
    }
}
