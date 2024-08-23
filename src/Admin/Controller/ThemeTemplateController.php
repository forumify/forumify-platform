<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Core\Entity\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('themes', 'themes')]
class ThemeTemplateController extends AbstractController
{
    #[Route('/{id<\d+>}/templates', '_templates')]
    public function templates(Theme $theme): Response
    {
        return $this->render('@Forumify/admin/theme/templates.html.twig', [
            'theme' => $theme,
        ]);
    }


}
