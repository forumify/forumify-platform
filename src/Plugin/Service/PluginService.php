<?php

declare(strict_types=1);

namespace Forumify\Plugin\Service;

use Composer\InstalledVersions;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\Application\Service\PluginService as ApplicationPluginService;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PluginService
{
    /**
     * Memoized plugin composer.json's
     *
     * @var array<string, array>|null
     */
    private ?array $plugins = null;

    /**
     * Memoized "composer outdated" information
     *
     * @var array<string, array>|null
     */
    private ?array $latestVersions = null;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $rootDir,
        private readonly PluginRepository $pluginRepository,
    ) {
    }

    public function refresh(): void
    {
        /** @var array<Plugin> $allPlugins */
        $allPlugins = [];

        $installedPlugins = $this->getInstalledPlugins();
        $installedPluginClasses = [];
        foreach ($installedPlugins as $composerJson) {
            $installedPluginClasses[] = $composerJson['extra']['forumify-plugin-class'];
        }

        /** @var array<Plugin> $knownPlugins */
        $knownPlugins = $this->pluginRepository->findAll();
        foreach ($knownPlugins as $knownPlugin) {
            if (!in_array($knownPlugin->getPluginClass(), $installedPluginClasses, true)) {
                $this->pluginRepository->remove($knownPlugin, false);
                continue;
            }
            $allPlugins[] = $knownPlugin;
        }

        $knownPluginClasses = array_map(static fn (Plugin $plugin) => $plugin->getPluginClass(), $knownPlugins);
        foreach ($installedPlugins as $package => $composerJson) {
            $pluginClass = $composerJson['extra']['forumify-plugin-class'];
            if (in_array($pluginClass, $knownPluginClasses, true)) {
                continue;
            }

            $plugin = new Plugin();
            $plugin->setPackage($package);
            $plugin->setPluginClass($pluginClass);
            $plugin->setVersion(InstalledVersions::getPrettyVersion($package));
            $plugin->setLatestVersion('0.0.0');
            $this->pluginRepository->save($plugin, false);

            $allPlugins[] = $plugin;
        }

        $this->pluginRepository->flush();

        $latestVersions = $this->getLatestVersions();
        foreach ($allPlugins as $plugin) {
            $versions = $latestVersions[$plugin->getPackage()] ?? null;
            if ($versions === null) {
                continue;
            }

            $plugin->setVersion($versions['version']);
            $plugin->setLatestVersion($versions['latest']);
        }
        $this->pluginRepository->saveAll($allPlugins);
    }

    public function getLatestVersions(): array
    {
        if ($this->latestVersions !== null) {
            return  $this->latestVersions;
        }

        $this->latestVersions = ApplicationPluginService::getLatestVersions($this->rootDir);
        return $this->latestVersions;
    }

    /**
     * @return array<string, array> list of composer.json's for installed forumify plugins
     */
    private function getInstalledPlugins(): array
    {
        if ($this->plugins !== null) {
            return $this->plugins;
        }

        $packages = InstalledVersions::getInstalledPackagesByType('forumify-plugin');
        $packages = array_unique($packages);

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

        $this->plugins = $foundPlugins;
        return $this->plugins;
    }
}
