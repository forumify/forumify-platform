<?php

declare(strict_types=1);

namespace Forumify\Core\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Forumify\Core\Api\Dto\MarkAsRead;
use Forumify\Core\Api\Dto\ReadMarkerState;
use Forumify\Core\Api\Processor\MarkAsReadProcessor;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/read-markers/mark-as-read',
            security: 'is_granted("ROLE_USER")',
            name: 'forumify_api_read_marker_mark_as_read',
            processor: MarkAsReadProcessor::class,
            input: MarkAsRead::class,
        ),
    ],
)]
class ReadMarker
{
    /**
     * @param array<ReadMarkerState> $markers
     */
    public function __construct(
        /** @var array<ReadMarkerState> */
        #[Groups('ReadMarker')]
        public array $markers = [],
    ) {
    }
}
