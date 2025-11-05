<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Symfony\Component\HttpFoundation\Request;

class ACLParameters
{
    /**
     * @param string $entity
     * @param string $entityId
     * @param string $returnPath
     * @param array<mixed> $returnParameters
     */
    public function __construct(
        public readonly string $entity,
        public readonly string $entityId,
        public readonly string $returnPath,
        public readonly array $returnParameters = [],
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->get('entity'),
            $request->get('entityId'),
            $request->get('returnPath'),
            $request->get('returnParameters') ?? [],
        );
    }
}
