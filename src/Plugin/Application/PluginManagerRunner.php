<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application;

use Forumify\Plugin\Application\Controller\ActivatePluginController;
use Forumify\Plugin\Application\Controller\DeactivatePluginController;
use Forumify\Plugin\Application\Controller\InstallPluginController;
use Forumify\Plugin\Application\Controller\UninstallPluginController;
use Forumify\Plugin\Application\Controller\UpdatePluginController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Runtime\RunnerInterface;

/**
 * Self-contained application runner that can work without Symfony container.
 * Plugins enable/disable Symfony bundles dynamically, doing so from within Symfony will cause critical errors.
 */
class PluginManagerRunner implements RunnerInterface
{
    public function __construct(private readonly array $context)
    {
    }

    public function run(): int
    {
        $request = Request::createFromGlobals();
        try {
            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $ex) {
            (new JsonResponse(['error' => 'unable to decode body: ' . $ex->getMessage()]))
                ->prepare($request)
                ->send();

            return 0;
        }

        $action = $body['action'] ?? null;
        $handler = match ($action) {
            'activate' => (new ActivatePluginController($this->context))(...),
            'deactivate' => (new DeactivatePluginController($this->context))(...),
            'update' => (new UpdatePluginController($this->context))(...),
            'install' => (new InstallPluginController($this->context))(...),
            'uninstall' => (new UninstallPluginController($this->context))(...),
            default => static fn () => new JsonResponse(['error' => 'action not found'], Response::HTTP_NOT_FOUND),
        };

        $handler($body)
            ->prepare($request)
            ->send();

        return 0;
    }
}
