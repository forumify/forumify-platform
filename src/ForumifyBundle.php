<?php

declare(strict_types=1);

namespace Forumify;

use Forumify\Plugin\AbstractForumifyPlugin;
use Forumify\Plugin\PluginMetadata;
use Forumify\Plugin\PluginPermissions;

class ForumifyBundle extends AbstractForumifyPlugin
{
    public function getPluginMetadata(): PluginMetadata
    {
        return new PluginMetadata(
            'Forumify Platform',
            'Forumify',
        );
    }

    public function getPluginPermissions(): PluginPermissions
    {
        $perms = [
            'forumify' => [
                'admin' => [
                    'user' => ['view', 'modify', 'delete'],
                ]
            ]
        ];




        return new PluginPermissions($perms);
    }
}
