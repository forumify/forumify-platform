<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance\Disclosure;

class RecipientDisclosure
{
    /**
     * @param array<string> $dataShared
     */
    public function __construct(
        public readonly string $name,
        public readonly string $purpose,
        public readonly array $dataShared = [],
        public readonly bool $outsideEea = false,
        public readonly ?string $privacyPolicyUrl = null,
    ) {
    }
}
