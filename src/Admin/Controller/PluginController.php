<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Firebase\JWT\JWT;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\Service\PluginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/plugins', 'plugin_')]
class PluginController extends AbstractController
{
    public function __construct(
        private readonly PluginRepository $pluginRepository,
        private readonly PluginService $pluginService,
        private readonly string $appSecret,
    ) {
    }

    #[Route('', 'list')]
    public function list(): Response
    {
        $activePlugins = $this->pluginRepository->findBy(['active' => true], ['package' => 'ASC']);
        $inactivePlugins = $this->pluginRepository->findBy(['active' => false], ['package' => 'ASC']);

        $latestVersions = $this->pluginService->getLatestVersions();
        $platformVersions = $latestVersions['forumify/forumify-platform'] ?? null;

        /** @var User $user */
        $user = $this->getUser();
        $ajaxAuthToken = JWT::encode([
            'sub' => $user->getId(),
            'exp' => (new \DateTime())->add(new \DateInterval('P1D'))->getTimestamp(),
            'resource_access' => ['plugin-manager'],
        ], $this->appSecret, 'HS256');

        return $this->render('@Forumify/admin/plugin/plugin_manager.html.twig', [
            'activePlugins' => $activePlugins,
            'inactivePlugins' => $inactivePlugins,
            'pluginService' => $this->pluginService,
            'platformVersions' => $platformVersions,
            'ajaxAuthToken' => $ajaxAuthToken,
        ]);
    }

    #[Route('/refresh', 'refresh')]
    public function refresh(): Response
    {
        $this->pluginService->refresh();
        return $this->redirectToRoute('forumify_admin_plugin_list');
    }
}
