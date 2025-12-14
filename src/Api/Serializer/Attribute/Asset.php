<?php

declare(strict_types=1);

namespace Forumify\Api\Serializer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Asset
{
    public function __construct(
        public readonly string $storage,
    ) {
    }
}
