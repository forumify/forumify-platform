<?php

declare(strict_types=1);

namespace Forumify\Page\Service;

use Forumify\Core\Service\MarkdownParser;
use Forumify\Page\Entity\Page;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

class PageMarkdownParser
{
    private const CACHE_KEY = 'markdown.cache.';

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly MarkdownParser $parser,
    ) {
    }

    public function parse(Page $page): string
    {
        $pageCacheKey = self::CACHE_KEY . $page->getUrlKey() . '-' . $page->getUpdatedAt()?->format('d-m-Y-H-i-s');

        try {
            return $this->cache->get($pageCacheKey, fn () => $this->parser->parse($page->getSource()));
        } catch (InvalidArgumentException) {
            // ok
        }
        return '';
    }
}
