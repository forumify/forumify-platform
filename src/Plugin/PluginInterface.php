<?php

declare(strict_types=1);

namespace Forumify\Plugin;

interface PluginInterface
{
    public function getPluginMetadata(): PluginMetadata;
}
