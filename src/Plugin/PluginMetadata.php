<?php

declare(strict_types=1);

namespace Forumify\Plugin;

class PluginMetadata
{
    public function __construct(
        public readonly string $name,
        public readonly string $author,
        public readonly string $description = '',
        public readonly ?string $homepage = null,
        public readonly ?string $settingsRoute = null,
    ) {
    }
}
