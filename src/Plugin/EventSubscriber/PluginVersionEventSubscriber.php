<?php

declare(strict_types=1);

namespace Forumify\Plugin\EventSubscriber;

use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\Attribute\PluginVersion;
use Forumify\Plugin\Service\PluginVersionChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PluginVersionEventSubscriber extends AbstractController implements EventSubscriberInterface
{
    public function __construct(
        private readonly PluginVersionChecker $pluginVersionChecker,
        private readonly PluginRepository $pluginRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'checkPluginVersion',
        ];
    }

    public function checkPluginVersion(ControllerEvent $event): void
    {
        /** @var array<PluginVersion> $pluginVersionAttributes */
        $pluginVersionAttributes = $event->getAttributes(PluginVersion::class);
        if (empty($pluginVersionAttributes)) {
            return;
        }

        foreach ($pluginVersionAttributes as $attribute) {
            if (!$this->pluginVersionChecker->isVersionInstalled($attribute->plugin, $attribute->versions)) {
                $event->setController($this->handleVersionNotInstalled($attribute));
                return;
            }
        }
    }

    private function handleVersionNotInstalled(PluginVersion $requiredVersion): callable
    {
        return function () use ($requiredVersion): Response {
            $plugin = $this->pluginRepository->findOneBy(['package' => $requiredVersion->plugin]);
            return $this->render('@Forumify/frontend/plugin/version_not_installed.html.twig', [
                'requiredVersion' => $requiredVersion,
                'plugin' => $plugin,
            ]);
        };
    }
}
