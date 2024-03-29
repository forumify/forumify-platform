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

        $menu->addItem(new MenuItem('Dashboard', $url('forumify_admin_dashboard'), ['icon' => 'ph ph-gauge']));
        $menu->addItem(new MenuItem('Configuration', $url('forumify_admin_configuration'), ['icon' => 'ph ph-gear']));
        $menu->addItem(new MenuItem('Users', $url('forumify_admin_user_list'), ['icon' => 'ph ph-user-list']));
        $menu->addItem(new MenuItem('Forums', $url('forumify_admin_forum'), ['icon' => 'ph ph-chats']));
        $menu->addItem(new MenuItem('CMS', $url('forumify_admin_cms_menu'), ['icon' => 'ph ph-files']));
        $menu->addItem(new MenuItem('Plugins', $url('forumify_admin_plugins'), ['icon' => 'ph ph-plugs']));
        $menu->addItem(new MenuItem('Settings', $url('forumify_admin_settings'), ['icon' => 'ph ph-wrench']));
    }
}
