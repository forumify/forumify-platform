<?php

declare(strict_types=1);

namespace Forumify\Core\Api\Dto;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class MarkAsRead
{
    #[Assert\NotNull]
    #[Assert\Valid]
    #[Groups('ReadMarker')]
    public ?ReadMarkerReference $subject = null;

    /**
     * The markers to report back on. Reading a forum also reads its sub forums and their topics, so
     * markers other than the subject can change.
     *
     * @var array<ReadMarkerReference>
     */
    #[Assert\Valid]
    #[Assert\Count(max: 1000)]
    #[Groups('ReadMarker')]
    public array $markers = [];
}
