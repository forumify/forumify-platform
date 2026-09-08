<?php

declare(strict_types=1);

namespace Forumify\Core\Api\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

class ReadMarkerState extends ReadMarkerReference
{
    #[Groups('ReadMarker')]
    public bool $read = false;

    public static function create(string $type, int $id, bool $read): self
    {
        $state = new self();
        $state->type = $type;
        $state->id = $id;
        $state->read = $read;

        return $state;
    }
}
