<?php

declare(strict_types=1);

namespace Forumify\Api\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Forumify\Plugin\Attribute\PluginVersion;
use Forumify\Plugin\Service\PluginVersionChecker;
use ReflectionClass;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * API requests are handled by API-Platform's own controllers, so the attributes on a resource class are never
 * seen by the controller based PluginVersionEventSubscriber. This applies #[PluginVersion] to API resources.
 */
#[AsEventListener(KernelEvents::REQUEST, priority: EventPriorities::PRE_READ)]
class PluginVersionListener
{
    public function __construct(private readonly PluginVersionChecker $pluginVersionChecker)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $resourceClass = $event->getRequest()->attributes->get('_api_resource_class');
        if (!is_string($resourceClass) || !class_exists($resourceClass)) {
            return;
        }

        $attributes = (new ReflectionClass($resourceClass))->getAttributes(PluginVersion::class);
        foreach ($attributes as $attribute) {
            $requiredVersion = $attribute->newInstance();
            if ($this->pluginVersionChecker->isVersionInstalled($requiredVersion->plugin, $requiredVersion->versions)) {
                continue;
            }

            $event->setResponse(new JsonResponse([
                'error' => $this->createErrorMessage($requiredVersion),
            ], Response::HTTP_FORBIDDEN));
            return;
        }
    }

    private function createErrorMessage(PluginVersion $requiredVersion): string
    {
        if (empty($requiredVersion->versions)) {
            return "This operation requires {$requiredVersion->plugin} to be active.";
        }

        $versions = implode(', ', $requiredVersion->versions);
        return "This operation requires {$requiredVersion->plugin} with one of the following versions: {$versions}.";
    }
}
