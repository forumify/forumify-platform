<?php

declare(strict_types=1);

namespace Forumify\Plugin;

use Forumify\Plugin\Entity\Plugin;

abstract class AbstractForumifyTheme implements PluginInterface
{
    abstract public function getThemeConfig(): ThemeConfig;
}
