<?php

declare(strict_types=1);

namespace Forumify\Cms\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CMSExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('resource', [CMSExtensionRuntime::class, 'resource']),
            new TwigFunction('snippet', [CMSExtensionRuntime::class, 'snippet'], ['is_safe' => ['html']]),
            new TwigFunction('widget', [CMSExtensionRuntime::class, 'widget'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('widget_template', [CMSExtensionRuntime::class, 'widgetTemplate']),
        ];
    }
}
