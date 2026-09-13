<?php

declare(strict_types=1);

namespace Forumify\Core\Api\Dto;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Points at a read marker subject without exposing the entity class it belongs to.
 */
class ReadMarkerReference
{
    #[Assert\NotBlank]
    #[Groups('ReadMarker')]
    public string $type = '';

    #[Assert\Positive]
    #[Groups('ReadMarker')]
    public int $id = 0;
}
