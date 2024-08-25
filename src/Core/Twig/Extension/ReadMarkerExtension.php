<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ReadMarkerExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('read', [ReadMarkerRuntime::class, 'read']),
        ];
    }
}
