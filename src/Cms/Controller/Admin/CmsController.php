<?php

declare(strict_types=1);

namespace Forumify\Cms\Controller\Admin;

use Forumify\Core\MenuBuilder\Menu;
use Forumify\Core\MenuBuilder\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('forumify.admin.cms.view')]
class CmsController extends AbstractController
{
    #[Route('', 'menu')]
    public function __invoke(TranslatorInterface $t, UrlGeneratorInterface $u)
    {
        $menu = new Menu($t->trans('admin.cms.title'), items: [
            new MenuItem($t->trans('admin.cms.pages.title'), $u->generate('forumify_admin_cms_page_list'), [
                'icon' => 'ph ph-file-html',
                'permission' => 'forumify.admin.cms.pages.view'
            ]),
            new MenuItem($t->trans('admin.cms.resources.title'), $u->generate('forumify_admin_cms_resource_list'), [
                'icon' => 'ph ph-paperclip',
                'permission' => 'forumify.admin.cms.resources.view'
            ]),
            new MenuItem($t->trans('admin.cms.snippets.title'), $u->generate('forumify_admin_cms_snippet_list'), [
                'icon' => 'ph ph-file-code',
                'permission' => 'forumify.admin.cms.snippets.view'
            ]),
        ]);

        $menu->sortByLabel();
        return $this->render('@Forumify/admin/directory.html.twig', [
            'directory' => $menu,
        ]);
    }
}
