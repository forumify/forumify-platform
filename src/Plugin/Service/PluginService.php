<?php

declare(strict_types=1);

namespace Forumify\Plugin\Service;

use Composer\InstalledVersions;
use Forumify\Admin\Service\MarketplaceService;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\Application\Service\PluginService as ApplicationPluginService;
use Forumify\Plugin\Entity\Plugin;
use Forumify\Plugin\Event\PluginRefreshedEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PluginService
{
    /**
     * Memoized "composer outdated" information
     *
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $latestVersions = null;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $rootDir,
        private readonly PluginRepository $pluginRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MarketplaceService $marketplaceService,
    ) {
    }

    public function refresh(): void
    {
        /** @var array<Plugin> $allPlugins */
        $allPlugins = $this->pluginRepository->findAll();
        $knownPlugins = array_combine(
            array_map(static fn (Plugin $plugin) => $plugin->getPackage(), $allPlugins),
            $allPlugins,
        );

        $marketplaceSubscriptionVersions = $this->getMarketplaceSubscriptionVersions();

        $plugins = [];
        $installedPlugins = $this->getInstalledPlugins();
        foreach ($installedPlugins as $package => $composerJson) {
            $plugin = $knownPlugins[$package] ?? null;
            if ($plugin === null) {
                $type = match ($composerJson['type']) {
                    'forumify-plugin' => Plugin::TYPE_PLUGIN,
                    'forumify-theme' => Plugin::TYPE_THEME,
                    default => null,
                };

                if ($type === null) {
                    continue;
                }

                $plugin = new Plugin();
                $plugin->setType($type);
                $plugin->setPackage($package);
                $plugin->setVersion('0.0.0');
                $plugin->setLatestVersion('0.0.0');
            }

            $pluginClass = $composerJson['extra']['forumify-plugin-class'];
            $plugin->setPluginClass($pluginClass);

            $marketplaceVersion = $marketplaceSubscriptionVersions[$package] ?? null;
            $plugin->setSubscriptionVersion($marketplaceVersion);

            $this->pluginRepository->save($plugin, false);
            $this->eventDispatcher->dispatch(new PluginRefreshedEvent($plugin));

            $plugins[] = $plugin;
        }

        foreach ($knownPlugins as $package => $plugin) {
            if (!isset($installedPlugins[$package])) {
                $this->pluginRepository->remove($plugin, false);
            }
        }

        $this->pluginRepository->flush();

        $latestVersions = $this->getLatestVersions();
        foreach ($plugins as $plugin) {
            $versions = $latestVersions[$plugin->getPackage()] ?? null;
            if ($versions === null) {
                continue;
            }

            $plugin->setVersion($versions['version']);
            $plugin->setLatestVersion($versions['latest']);
        }
        $this->pluginRepository->saveAll($plugins);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getLatestVersions(): array
    {
        if ($this->latestVersions !== null) {
            return $this->latestVersions;
        }

        $this->latestVersions = ApplicationPluginService::getLatestVersions($this->rootDir);
        return $this->latestVersions;
    }

    /**
     * @return array<string, array<string, mixed>> list of composer.json's for installed forumify plugins
     */
    private function getInstalledPlugins(): array
    {
        $plugins = InstalledVersions::getInstalledPackagesByType('forumify-plugin');
        $themes = InstalledVersions::getInstalledPackagesByType('forumify-theme');
        $packages = array_unique(array_merge($plugins, $themes));

        $foundPlugins = [];
        foreach ($packages as $pluginPackage) {
            $composerJson = file_get_contents($this->rootDir . '/vendor/' . $pluginPackage . '/composer.json');
            if ($composerJson === false) {
                continue;
            }

            try {
                $decodedJson = json_decode($composerJson, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                // shouldn't occur since composer.json should be validated already
                continue;
            }

            $pluginClass = $decodedJson['extra']['forumify-plugin-class'] ?? null;
            if ($pluginClass === null) {
                continue;
            }

            $foundPlugins[$pluginPackage] = $decodedJson;
        }

        return $foundPlugins;
    }

    /**
     * @return array<string>
     */
    private function getMarketplaceSubscriptionVersions(): array
    {
        try {
            $customer = $this->marketplaceService->getCustomer();
        } catch (\Exception) {
            $customer = null;
        }

        if ($customer === null) {
            return [];
        }

        $subscriptions = [];
        foreach ($customer['subscriptions'] ?? [] as $subscription) {
            $subscriptions[$subscription['package']] = $subscription['versionKey'];
        }
        return $subscriptions;
    }
}
