<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application\Controller;

use Exception;
use Forumify\Plugin\Application\Service\PluginService;
use Symfony\Component\HttpFoundation\JsonResponse;

class UninstallPluginController
{
    private readonly PluginService $pluginService;

    public function __construct(array $context)
    {
        $this->pluginService = new PluginService($context);
    }

    public function __invoke(array $args): JsonResponse
    {
        try {
            $package = $this->pluginService->findPackageForPlugin($args['plugin']);
            $this->pluginService->uninstallPluginFromPackage($package);
            return new JsonResponse(['success' => true]);
        } catch (Exception $ex) {
            return new JsonResponse(['success' => false, 'error' => $ex->getMessage()]);
        }
    }
}
