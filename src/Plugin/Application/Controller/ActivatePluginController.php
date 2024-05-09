<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application\Controller;

use Forumify\Plugin\Application\Exception\PluginException;
use Forumify\Plugin\Application\Exception\UnbootableKernelException;
use Forumify\Plugin\Application\Service\PluginService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ActivatePluginController
{
    private readonly PluginService $pluginService;

    public function __construct(private readonly array $context)
    {
        $this->pluginService = new PluginService($this->context);
    }

    public function __invoke(array $args): Response
    {
        try {
            $this->pluginService->activate($args['plugin']);
            return new JsonResponse(['success' => true]);
        } catch (UnbootableKernelException $ex) {
            try {
                $this->pluginService->deactivate($args['plugin']);
            } catch (\Exception) {
                // welp,.. we tried..
            }
            return new JsonResponse(['success' => false, 'error' => $ex->getMessage()]);
        } catch (PluginException $ex) {
            return new JsonResponse(['success' => false, 'error' => $ex->getMessage()]);
        }
    }
}
