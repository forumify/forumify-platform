<?php

declare(strict_types=1);

namespace Forumify\Cms\Twig\Extension;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Extension\RuntimeExtensionInterface;

class CacheRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly CacheInterface $cache)
    {
    }

    public function cache(string $content, string $key, int $ttl): string
    {
        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($content, $ttl) {
                $item->expiresAfter($ttl);
                return $content;
            });
        } catch (InvalidArgumentException) {
            return $content;
        }
    }
}
