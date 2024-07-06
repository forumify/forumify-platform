<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Service\ThemeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/theme', 'theme_')]
class ThemeController extends AbstractController
{
    #[Route('/toggle', 'toggle')]
    public function __invoke(Request $request): Response
    {
        $cookie = $request->cookies->get(ThemeService::CURRENT_THEME_COOKIE);
        $current = $cookie ?: $request->get('preference', 'default');
        $new = $current === 'default' ? 'dark' : 'default';

        $target = $request->get('_target_path');
        $response = $target !== null
            ? $this->redirect($target)
            : $this->redirectToRoute('forumify_core_index');

        $response->headers->setCookie(new Cookie(
            ThemeService::CURRENT_THEME_COOKIE,
            $new,
        ));
        return $response;
    }
}
