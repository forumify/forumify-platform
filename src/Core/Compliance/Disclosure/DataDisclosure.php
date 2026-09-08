<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance\Disclosure;

class DataDisclosure
{
    /**
     * @param array<string> $fields
     */
    public function __construct(
        public readonly string $category,
        public readonly array $fields,
        public readonly string $purpose,
        public readonly LegalBasis $legalBasis,
        public readonly string $retention,
    ) {
    }
}
