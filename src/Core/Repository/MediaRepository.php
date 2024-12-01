<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Core\Entity\Media;

/**
 * @extends AbstractRepository<Media>
 */
class MediaRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Media::class;
    }
}
