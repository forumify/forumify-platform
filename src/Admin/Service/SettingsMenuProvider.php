<?php

declare(strict_types=1);

namespace Forumify\Admin\Service;

use Forumify\Core\MenuBuilder\Menu;
use Forumify\Core\MenuBuilder\MenuItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingsMenuProvider implements SettingsMenuProviderInterface
{
    public function provide(UrlGeneratorInterface $u, TranslatorInterface $t): Menu
    {
        return new Menu($t->trans('settings'), items: [
            new MenuItem($t->trans('admin.audit_logs.title'), $u->generate('forumify_admin_audit_logs_list'), [
                'icon' => 'ph ph-detective',
                'permission' => 'forumify.admin.settings.audit_logs.view',
            ]),
            new MenuItem($t->trans('roles'), $u->generate('forumify_admin_roles_list'), [
                'icon' => 'ph ph-lock-key',
                'permission' => 'forumify.admin.settings.roles.view',
            ]),
            new MenuItem($t->trans('reactions'), $u->generate('forumify_admin_reactions_list'), [
                'icon' => 'ph ph-smiley-wink',
                'permission' => 'forumify.admin.settings.reactions.view',
            ]),
            new MenuItem($t->trans('admin.badge.crud.plural'), $u->generate('forumify_admin_badges_list'), [
                'icon' => 'ph ph-medal-military',
                'permission' => 'forumify.admin.settings.badges.view',
            ]),
            new MenuItem($t->trans('admin.menu_builder.title'), $u->generate('forumify_admin_menu_builder'), [
                'icon' => 'ph ph-list',
                'permission' => 'forumify.admin.settings.menu_builder.manage',
            ]),
            new MenuItem('Plugins', $u->generate('forumify_admin_plugin_list'), [
                'icon' => 'ph ph-plugs',
                'permission' => 'forumify.admin.settings.plugins.manage',
            ]),
            new MenuItem('Themes', $u->generate('forumify_admin_themes_list'), [
                'icon' => 'ph ph-paint-brush',
                'permission' => 'forumify.admin.settings.themes.view',
            ]),
            new MenuItem($t->trans('admin.automations.title'), $u->generate('forumify_admin_automation_list'), [
                'icon' => 'ph ph-git-branch',
                'permission' => 'forumify.admin.settings.automations.view',
            ]),
            new MenuItem($t->trans('admin.o_auth_client.crud.plural'), $u->generate('forumify_admin_oauth_clients_list'), [
                'icon' => 'ph ph-hard-drives',
                'permission' => 'forumify.admin.settings.oauth_clients.view',
            ]),
            new MenuItem($t->trans('admin.identity_provider.crud.plural'), $u->generate('forumify_admin_identity_providers_list'), [
                'icon' => 'ph ph-identification-badge',
                'permission' => 'forumify.admin.settings.identity_providers.view',
            ]),
        ]);
    }
}
