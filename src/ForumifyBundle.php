<?php

declare(strict_types=1);

namespace Forumify;

use Forumify\Plugin\AbstractForumifyBundle;

class ForumifyBundle extends AbstractForumifyBundle
{
    public static function getPermissions(): array
    {
        return [
            'admin' => [
                'configuration' => [
                    'manage',
                ],
                'users' => [
                    'view',
                    'manage'
                ],
                'forums' => [
                    'manage',
                ],
                'cms' => [
                    'view',
                    'pages' => [
                        'view',
                        'manage'
                    ],
                    'resources' => [
                        'view',
                        'manage'
                    ],
                    'snippets' => [
                        'view',
                        'manage'
                    ]
                ],
                'settings' => [
                    'view',
                    'badges' => [
                        'view',
                        'manage'
                    ],
                    'menu_builder' => [
                        'manage'
                    ],
                    'plugins' => [
                        'manage'
                    ],
                    'reactions' => [
                        'view',
                        'manage'
                    ],
                    'roles' => [
                        'view',
                        'manage'
                    ],
                    'themes' => [
                        'view',
                        'manage'
                    ],
                ],
            ],
        ];
    }
}
