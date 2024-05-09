<?php

declare(strict_types=1);

namespace Forumify\Plugin;

abstract class AbstractForumifyPlugin extends AbstractForumifyBundle
{
    abstract public function getPluginMetadata(): PluginMetadata;
}
