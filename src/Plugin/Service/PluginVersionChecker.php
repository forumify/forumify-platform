<?php

declare(strict_types=1);

namespace Forumify\Plugin\Service;

use Forumify\Core\Repository\PluginRepository;

class PluginVersionChecker
{
    public function __construct(
        private readonly PluginRepository $pluginRepository,
    ) {
    }

    public function isVersionInstalled(string $pluginPackage, string|array $versions): bool
    {
        if (is_string($versions)) {
            $versions = [$versions];
        }

        $plugin = $this->pluginRepository->findOneBy(['package' => $pluginPackage]);
        if ($plugin === null) {
            return false;
        }
        return in_array($plugin->getSubscriptionVersion(), $versions, true);
    }
}
