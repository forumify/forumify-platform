<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Service\MarketplaceService;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Core\Service\TokenService;
use Forumify\Plugin\Entity\Plugin;
use Forumify\Plugin\Service\PluginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/plugins', 'plugin_')]
#[IsGranted('forumify.admin.settings.plugins.manage')]
class PluginController extends AbstractController
{
    public function __construct(
        private readonly PluginRepository $pluginRepository,
        private readonly PluginService $pluginService,
        private readonly TokenService $tokenService,
    ) {
    }

    #[Route('', 'list')]
    public function list(MarketplaceService $marketplaceService): Response
    {
        $activePlugins = $this->pluginRepository->findBy(['active' => true, 'type' => Plugin::TYPE_PLUGIN], ['package' => 'ASC']);
        $inactivePlugins = $this->pluginRepository->findBy(['active' => false, 'type' => Plugin::TYPE_PLUGIN], ['package' => 'ASC']);
        $themes = $this->pluginRepository->findBy(['type' => Plugin::TYPE_THEME], ['package' => 'ASC']);

        $latestVersions = $this->pluginService->getLatestVersions();
        $platformVersions = $latestVersions['forumify/forumify-platform'] ?? null;

        /** @var User $user */
        $user = $this->getUser();
        $ajaxAuthToken = $this->tokenService->createJwt(
            $user,
            (new \DateTime())->add(new \DateInterval('P1D')),
            ['plugin-manager'],
        );

        try {
            $marketplaceCustomer = $marketplaceService->getCustomer();
        } catch (\Exception) {
            $marketplaceCustomer = null;
        }

        $marketplacePlugins = [];
        if ($marketplaceCustomer !== null) {
            foreach ($marketplaceCustomer['subscriptions'] as $subscription) {
                $isInstalled = isset($latestVersions[$subscription['package']]);
                $subscription['installed'] = $isInstalled;

                $marketplacePlugins[] = $subscription;
            }
        }

        return $this->render('@Forumify/admin/plugin/plugin_manager.html.twig', [
            'activePlugins' => $activePlugins,
            'inactivePlugins' => $inactivePlugins,
            'themes' => $themes,
            'pluginService' => $this->pluginService,
            'platformVersions' => $platformVersions,
            'ajaxAuthToken' => $ajaxAuthToken,
            'marketplaceCustomer' => $marketplaceCustomer,
            'marketplacePlugins' => $marketplacePlugins,
        ]);
    }

    #[Route('/refresh', 'refresh')]
    public function refresh(): Response
    {
        $this->pluginService->refresh();
        return $this->redirectToRoute('forumify_admin_plugin_list');
    }
}
