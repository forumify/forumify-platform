<?php

declare(strict_types=1);

namespace Forumify\Plugin;

class ThemeVar
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly ThemeVarType $type,
        public readonly string $defaultValue,
        public readonly ?string $defaultDarkValue = null,
        public readonly ?string $help = null,
    ) {
    }
}
