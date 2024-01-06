<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Plugin\PluginInterface;
use Forumify\Plugin\Service\LoadedPluginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PluginController extends AbstractController
{
    #[Route('/plugins', 'plugins')]
    public function __invoke(LoadedPluginService $pluginsService): Response
    {
        $pluginMetadatas = array_map(
            static fn (PluginInterface $plugin) => $plugin->getPluginMetadata(),
            $pluginsService->getLoadedPlugins()
        );

        return $this->render('@Forumify/admin/plugin/plugin.html.twig', [
            'pluginMetadatas' => $pluginMetadatas,
        ]);
    }
}
