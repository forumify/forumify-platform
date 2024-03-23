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
    public function __invoke(UrlGeneratorInterface $url, TranslatorInterface $trans): Response
    {
        $menu = new Menu(items: [
            new MenuItem($trans->trans('roles'), $url->generate('forumify_admin_role_list'), [
                'icon' => 'ph ph-lock-key',
            ]),
            new MenuItem($trans->trans('reactions'), $url->generate('forumify_admin_reaction_list'), [
                'icon' => 'ph ph-smiley-wink',
            ]),
            new MenuItem($trans->trans('admin.badges.badges'), $url->generate('forumify_admin_badge_list'), [
                'icon' => 'ph ph-medal-military'
            ])
        ]);

        $menu->sortByLabel();
        return $this->render('@Forumify/admin/settings/settings.html.twig', [
            'settingMenuItems' => $menu->getEntries(),
        ]);
    }
}
