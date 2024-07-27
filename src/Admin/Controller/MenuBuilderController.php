<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('menu-builder', 'menu_builder')]
#[IsGranted('forumify.admin.settings.menu_builder.manage')]
class MenuBuilderController extends AbstractController
{
    #[Route('/', '')]
    public function __invoke(Request $request): Response
    {
        return $this->render('@Forumify/admin/menu_builder/menu_builder.html.twig');
    }
}
