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
        $menu->addItem(new MenuItem('Dashboard', $this->urlGenerator->generate('forumify_admin_dashboard'), [
            'icon' => 'ph ph-gauge',
        ]));

        $menu->addItem(new MenuItem('Configuration', $this->urlGenerator->generate('forumify_admin_settings'), [
            'icon' => 'ph ph-gear',
        ]));

        $menu->addItem(new Menu('Security', [
            'icon' => 'ph ph-shield-chevron',
        ], [
            new MenuItem('Users', $this->urlGenerator->generate('forumify_admin_user_list'), [
                'icon' => 'ph ph-user-list',
            ]),
            new MenuItem('Roles', $this->urlGenerator->generate('forumify_admin_role_list'), [
                'icon' => 'ph ph-lock-key',
            ]),
        ]));

        $menu->addItem(new MenuItem('Forums', $this->urlGenerator->generate('forumify_admin_forum'), [
            'icon' => 'ph ph-chats',
        ]));

        $menu->addItem(new MenuItem('Pages', $this->urlGenerator->generate('forumify_admin_page'), [
            'icon' => 'ph ph-files',
        ]));
    }
}
