<?php

declare(strict_types=1);

namespace Forumify;

use Forumify\Plugin\AbstractForumifyBundle;

class ForumifyBundle extends AbstractForumifyBundle
{
    public static $permissions = [
        'forumify' => [
            'admin' => [
                'dashboard' => ['view'],
                'configuration' => ['view'],
                'users' => ['view'],
                'forums' => ['view'],
                'cms' => ['view'],
                'settings' => ['view']
            ]
        ]

    ];

    public static function getPermissions(): array

    {
        return self::$permissions;
    }
}
