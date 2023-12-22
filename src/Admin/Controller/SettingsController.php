<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Core\MenuBuilder\Menu;
use Forumify\Core\MenuBuilder\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SettingsController extends AbstractController
{
    #[Route('/settings', 'settings')]
    public function __invoke(UrlGeneratorInterface $urlGenerator): Response
    {
        $menu = new Menu();
        $menu->addItem(new MenuItem('Roles', $urlGenerator->generate('forumify_admin_role_list'), [
            'icon' => 'ph ph-lock-key'
        ]));
        $menu->addItem(new MenuItem('Reactions', $urlGenerator->generate('forumify_admin_reaction_list'), [
            'icon' => 'ph ph-smiley-wink'
        ]));

        $menu->sortByLabel();
        return $this->render('@Forumify/admin/settings/settings.html.twig', [
            'settingMenuItems' => $menu->getEntries(),
        ]);
    }
}
