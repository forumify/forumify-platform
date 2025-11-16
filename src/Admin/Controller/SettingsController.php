<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Service\SettingsMenuProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('forumify.admin.settings.view')]
class SettingsController extends AbstractController
{
    public function __construct(private readonly SettingsMenuProviderInterface $menuProvider)
    {
    }

    #[Route('/settings', 'settings')]
    public function __invoke(UrlGeneratorInterface $u, TranslatorInterface $t): Response
    {
        $menu = $this->menuProvider->provide($u, $t);

        $menu->sortByLabel();
        return $this->render('@Forumify/admin/directory.html.twig', [
            'directory' => $menu,
        ]);
    }
}
