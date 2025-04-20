<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\TopicImage;

class TopicImageRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return TopicImage::class;
    }
}
