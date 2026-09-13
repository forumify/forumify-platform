<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class ACLParameters
{
    /**
     * @param class-string $entity
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
        $entity = $request->query->get('entity');
        $entityId = $request->query->get('entityId');
        if (empty($entity) || !class_exists($entity) || empty($entityId)) {
            throw new InvalidArgumentException('entity and entityId are required.');
        }

        $returnPath = $request->query->get('returnPath', '');
        $returnParams = $request->query->all('returnParameters');

        return new self($entity, $entityId, $returnPath, $returnParams);
    }
}
