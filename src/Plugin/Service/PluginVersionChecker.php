<?php

declare(strict_types=1);

namespace Forumify\Plugin\Service;

use Forumify\Core\Repository\PluginRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PluginVersionChecker
{
    private array $installedVersionCache = [];

    public function __construct(
        private readonly PluginRepository $pluginRepository,
        #[Autowire('%kernel.environment%')]
        private readonly string $env,
    ) {
    }

    public function isVersionInstalled(string $pluginPackage, string|array $versions): bool
    {
        if (in_array($this->env, ['dev', 'test'])) {
            // no checks in unit tests or dev mode
            return true;
        }

        if (is_string($versions)) {
            $versions = [$versions];
        }

        if (isset($this->installedVersionCache[$pluginPackage])) {
            return $this->installedVersionCache[$pluginPackage];
        }

        $plugin = $this->pluginRepository->findOneBy(['package' => $pluginPackage]);
        if ($plugin === null) {
            $this->installedVersionCache[$pluginPackage] = false;
            return false;
        }

        $this->installedVersionCache[$pluginPackage] = in_array($plugin->getSubscriptionVersion(), $versions, true);
        return $this->installedVersionCache[$pluginPackage];
    }
}
