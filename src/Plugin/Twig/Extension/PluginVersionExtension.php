<?php

declare(strict_types=1);

namespace Forumify\Plugin\Twig\Extension;

use Forumify\Plugin\Service\PluginVersionChecker;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PluginVersionExtension extends AbstractExtension
{
    public function __construct(private readonly PluginVersionChecker $pluginVersionChecker)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('plugin_version', $this->pluginVersionChecker->isVersionInstalled(...)),
        ];
    }
}
