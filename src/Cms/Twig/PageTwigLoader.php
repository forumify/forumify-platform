<?php

declare(strict_types=1);

namespace Forumify\Cms\Twig;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Repository\PageRepository;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

class PageTwigLoader implements LoaderInterface
{
    public function __construct(private readonly PageRepository $pageRepository)
    {
    }

    public function getSourceContext(string $name): Source
    {
        $page = $this->getPage($name);
        if ($page === null) {
            throw new LoaderError("Template $name does not exist.");
        }

        return new Source($page->getTwig(), $name);
    }

    public function getCacheKey(string $name): string
    {
        return $name;
    }

    public function isFresh(string $name, int $time): bool
    {
        $page = $this->getPage($name);
        if ($page === null) {
            return false;
        }

        return ($page->getUpdatedAt() ?? $page->getCreatedAt())->getTimestamp() <= $time;
    }

    public function exists(string $name): bool
    {
        return $this->getPage($name) !== null;
    }

    private function getPage(string $name): ?Page
    {
        return $this->pageRepository->findOneByUrlKey($name);
    }
}
