<?php

declare(strict_types=1);

namespace Forumify\Plugin;

abstract class AbstractForumifyPlugin extends AbstractForumifyBundle implements PluginInterface
{
    /**
     * @return array The permissions that your plugin provides.
     *      For example `['admin' => ['feature' => 'view']]`
     *      Then you can use the permissions like "slugged-plugin-name.admin.feature.view"
     */
    public function getPermissions(): array
    {
        return [];
    }
}
