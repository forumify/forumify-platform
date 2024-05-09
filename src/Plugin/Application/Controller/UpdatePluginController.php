<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application\Controller;

use Exception;
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
            $this->pluginService->updatePlugin($args['plugin']);
            return new JsonResponse(['success' => true]);
        } catch (Exception $ex) {
            return new JsonResponse(['success' => false, 'error' => $ex->getMessage()]);
        }
    }
}
