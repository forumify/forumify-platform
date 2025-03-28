<?php

declare(strict_types=1);

namespace Forumify\Admin\MenuBuilder;

use Forumify\Core\MenuBuilder\Menu;
use Forumify\Core\MenuBuilder\MenuItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdminMenuBuilder implements AdminMenuBuilderInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function build(Menu $menu): void
    {
        $url = $this->urlGenerator->generate(...);

        $menu
            ->addItem(new MenuItem('Dashboard', $url('forumify_admin_dashboard'), [
                'icon' => 'ph ph-gauge',
            ]))
            ->addItem(new MenuItem('Configuration', $url('forumify_admin_configuration'), [
                'icon' => 'ph ph-gear',
                'permission' => 'forumify.admin.configuration.manage',
            ]))
            ->addItem(new MenuItem('Users', $url('forumify_admin_users_list'), [
                'icon' => 'ph ph-user-list',
                'permission' => 'forumify.admin.users.view',
            ]))
            ->addItem(new MenuItem('Forums', $url('forumify_admin_forum'), [
                'icon' => 'ph ph-chats',
                'permission' => 'forumify.admin.forums.manage',
            ]))
            ->addItem(new MenuItem('CMS', $url('forumify_admin_cms_menu'), [
                'icon' => 'ph ph-files',
                'permission' => 'forumify.admin.cms.view',
            ]))
            ->addItem(new MenuItem('Settings', $url('forumify_admin_settings'), [
                'icon' => 'ph ph-wrench',
                'permission' => 'forumify.admin.settings.view',
            ]));
    }
}
