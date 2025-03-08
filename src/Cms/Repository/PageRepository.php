<?php

declare(strict_types=1);

namespace Forumify\Cms\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Cms\Entity\Page;

class PageRepository extends AbstractRepository
{
    private array $pageMemo = [];

    public static function getEntityClass(): string
    {
        return Page::class;
    }

    public function findOneByUrlKey(string $urlKey): ?Page
    {
        if (isset($this->pageMemo[$urlKey])) {
            return $this->pageMemo[$urlKey];
        }
        $this->pageMemo[$urlKey] = $this->findOneBy(['urlKey' => $urlKey]);
        return $this->pageMemo[$urlKey];
    }

    public function findOneBySlug(string $slug): ?Page
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
