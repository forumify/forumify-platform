<?php

declare(strict_types=1);

namespace Forumify\Page\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Page\Entity\Page;

class PageRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Page::class;
    }

    public function findOneByUrlKey(string $urlKey): ?Page
    {
        return $this->findOneBy(['urlKey' => $urlKey]);
    }

    public function findOneBySlug(string $slug): ?Page
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
