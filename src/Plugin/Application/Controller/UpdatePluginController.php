<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application\Controller;

use Exception;
use Forumify\Plugin\Application\Exception\PluginException;
use Forumify\Plugin\Application\Service\PluginService;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdatePluginController
{
    private readonly PluginService $pluginService;

    public function __construct(array $context)
    {
        $this->pluginService = new PluginService($context);
    }

    public function __invoke(array $args): JsonResponse
    {
        try {
            $this->handleUpdate($args);
            return new JsonResponse(['success' => true]);
        } catch (Exception $ex) {
            return new JsonResponse(['success' => false, 'error' => $ex->getMessage()]);
        }
    }

    /**
     * @throws PluginException
     */
    private function handleUpdate(array $args): void
    {
        if ($args['plugin'] ?? false) {
            $package = $this->pluginService->findPackageForPlugin($args['plugin']);
            $this->pluginService->updatePackage($package);
            return;
        }

        if ($args['platform'] ?? false) {
            $this->pluginService->updatePackage('forumify/forumify-platform');
            return;
        }

        $this->pluginService->updateAll();
    }
}
