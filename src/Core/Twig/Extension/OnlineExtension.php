<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class OnlineExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('online', [OnlineExtensionRuntime::class, 'isOnline']),
        ];
    }
}
