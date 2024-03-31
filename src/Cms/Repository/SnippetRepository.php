<?php

declare(strict_types=1);

namespace Forumify\Cms\Repository;

use Forumify\Cms\Entity\Snippet;
use Forumify\Core\Repository\AbstractRepository;

class SnippetRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Snippet::class;
    }
}
