<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Core\MenuBuilder\Menu;
use Forumify\Core\MenuBuilder\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('forumify.admin.settings.view')]
class SettingsController extends AbstractController
{
    #[Route('/settings', 'settings')]
    public function __invoke(UrlGeneratorInterface $u, TranslatorInterface $t): Response
    {
        $menu = new Menu($t->trans('settings'), items: [
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
            new MenuItem($t->trans('admin.calendar.crud.plural'), $u->generate('forumify_admin_calendars_list'), [
                'icon' => 'ph ph-calendar',
                'permission' => 'forumify.admin.settings.calendars.view',
            ]),
            new MenuItem($t->trans('admin.automations.title'), $u->generate('forumify_admin_automation_list'), [
                'icon' => 'ph ph-git-branch',
                'permission' => 'forumify.admin.settings.automations.view'
            ])
        ]);

        $menu->sortByLabel();
        return $this->render('@Forumify/admin/directory.html.twig', [
            'directory' => $menu,
        ]);
    }
}
