<?php

declare(strict_types=1);

namespace Forumify\Core\Attribute;

use Symfony\Component\Routing\Route;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsFrontend
{
    public function __construct(
        public readonly string $name,
        public readonly Route $route,
        public readonly string $template,
        public readonly ?string $identifier = null,
        public readonly ?string $permission = null,
    ) {
    }
}
