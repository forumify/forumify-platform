<?php

declare(strict_types=1);

namespace Forumify\Cms\Controller\Admin;

use Forumify\Core\MenuBuilder\Menu;
use Forumify\Core\MenuBuilder\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CmsController extends AbstractController
{
    #[Route('', 'menu')]
    public function __invoke(TranslatorInterface $t, UrlGeneratorInterface $u)
    {
        $menu = new Menu(items: [
            new MenuItem($t->trans('admin.cms.pages.title'), $u->generate('forumify_admin_cms_page_list'), [
                'icon' => 'ph ph-file-html',
            ]),
            new MenuItem($t->trans('admin.cms.resources.title'), '', [
                'icon' => 'ph ph-paperclip',
            ]),
            new MenuItem($t->trans('admin.cms.snippets.title'), '', [
                'icon' => 'ph ph-file-code',
            ]),
        ]);

        $menu->sortByLabel();
        return $this->render('@Forumify/admin/cms/cms.html.twig', [
            'items' => $menu->getEntries(),
        ]);
    }
}
