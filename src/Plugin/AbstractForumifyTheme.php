<?php

declare(strict_types=1);

namespace Forumify\Plugin;

abstract class AbstractForumifyTheme implements PluginInterface
{
    public function getThemeConfig(): ThemeConfig
    {
        return new ThemeConfig();
    }

    /**
     * @return array<string>
     */
    public function getStylesheets(): array
    {
        return [];
    }
}
