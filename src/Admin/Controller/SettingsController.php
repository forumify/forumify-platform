<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Core\MenuBuilder\Menu;
use Forumify\Core\MenuBuilder\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingsController extends AbstractController
{
    #[Route('/settings', 'settings')]
    public function __invoke(UrlGeneratorInterface $u, TranslatorInterface $t): Response
    {
        $menu = new Menu(items: [
            new MenuItem($t->trans('roles'), $u->generate('forumify_admin_role_list'), [
                'icon' => 'ph ph-lock-key',
            ]),
            new MenuItem($t->trans('reactions'), $u->generate('forumify_admin_reaction_list'), [
                'icon' => 'ph ph-smiley-wink',
            ]),
            new MenuItem($t->trans('admin.badges.badges'), $u->generate('forumify_admin_badge_list'), [
                'icon' => 'ph ph-medal-military'
            ]),
            new MenuItem($t->trans('admin.menu_builder.title'), $u->generate('forumify_admin_menu_builder'), [
                'icon' => 'ph ph-list'
            ]),
        ]);

        $menu->sortByLabel();
        return $this->render('@Forumify/admin/settings/settings.html.twig', [
            'items' => $menu->getEntries(),
        ]);
    }
}
