<?php

declare(strict_types=1);

namespace Forumify\Cms\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CacheExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('cache', [CacheRuntime::class, 'cache'], ['is_safe' => ['html']]),
        ];
    }
}
