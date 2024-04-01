<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('menu-builder', 'menu_builder')]
class MenuBuilderController extends AbstractController
{
    #[Route('/', '')]
    public function __invoke(): Response
    {
        return $this->render('@Forumify/admin/menu_builder/menu_builder.html.twig');
    }
}
