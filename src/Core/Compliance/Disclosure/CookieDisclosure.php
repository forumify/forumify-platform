<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance\Disclosure;

class CookieDisclosure
{
    public function __construct(
        public readonly string $name,
        public readonly string $purpose,
        public readonly string $lifetime,
        public readonly CookieCategory $category = CookieCategory::Necessary,
        public readonly ?string $provider = null,
    ) {
    }
}
