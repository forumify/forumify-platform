<?php

declare(strict_types=1);

namespace Forumify\Plugin\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class PluginVersion
{
    /**
     * @var array
     */
    public readonly array $versions;

    public function __construct(
        public readonly string $plugin,
        array|string $versions
    ) {
        $this->versions = is_string($versions) ? [$versions] : $versions;
    }
}