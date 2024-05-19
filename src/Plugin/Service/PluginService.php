<?php

declare(strict_types=1);

namespace Forumify\Plugin\Service;

use Composer\InstalledVersions;
use Forumify\Core\Repository\PermissionRepository;
use Forumify\Core\Repository\PluginRepository;
use Forumify\ForumifyBundle;
use Forumify\Plugin\AbstractForumifyPlugin;
use Forumify\Plugin\Application\Service\PluginService as ApplicationPluginService;
use Forumify\Plugin\Entity\Permission;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PluginService
{
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
        private readonly PermissionRepository $permissionRepository,
        private readonly ForumifyBundle $forumifyBundle,
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
        $this->forumifyPermissions();


        $plugins = [];
        $installedPlugins = $this->getInstalledPlugins();
        foreach ($installedPlugins as $package => $composerJson) {
            $plugin = $knownPlugins[$package] ?? null;
            if ($plugin === null) {
                $plugin = new Plugin();
                $plugin->setPackage($package);
                $plugin->setVersion('0.0.0');
                $plugin->setLatestVersion('0.0.0');
            }

            $pluginClass = $composerJson['extra']['forumify-plugin-class'];
            $plugin->setPluginClass($pluginClass);

            $this->pluginRepository->save($plugin, false);
            $plugins[] = $plugin;

            if (class_exists($pluginClass)) {
                $pluginInstance = new $pluginClass();
                if ($pluginInstance instanceof AbstractForumifyPlugin) {
                    $permissions = $pluginInstance->getPluginPermissions()->getPermissions();

                    $currentPermissions = $this->permissionRepository->findBy(['plugin' => $plugin]);

                    foreach ($currentPermissions as $currentPermission) {
                        if (!in_array($currentPermission->getPermission(), $permissions)) {
                            $this->permissionRepository->remove($currentPermission);
                        }
                    }

                    foreach ($permissions as $pluginPermission) {
                        $existingPermission = $this->permissionRepository->findOneBy(['plugin' => $plugin, 'permission' => $pluginPermission]);
                        if ($existingPermission === null) {
                            $permission = new Permission();
                            $permission->setPlugin($plugin);
                            $permission->setPermission($pluginPermission);

                            $this->permissionRepository->save($permission);
                        }
                    }
                }
            }

            $permissions = $this->permissionRepository->findAll();

            foreach ($permissions as $permission) {
                $plugin = $permission->getPlugin();
                if ($plugin === null || !$this->pluginRepository->find($plugin->getId())) {
                    $this->permissionRepository->remove($permission);
                }
            }
            $this->permissionRepository->flush();
        }

        foreach ($knownPlugins as $package => $plugin) {
            if ($package === 'forumify/forumify-platform') {
                continue;
            }
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

        return $foundPlugins;
    }

    private function forumifyPermissions(): void
    {
        $plugin = $this->pluginRepository->findOneBy(['package' => 'forumify/forumify-platform']);

        if (!$plugin) {
            $forumify = new Plugin();
            $forumify->setPluginClass('Forumify\ForumifyBundle');
            $forumify->setPackage('forumify/forumify-platform');
            $forumify->setLatestVersion('dev-master');
            $forumify->setVersion('dev-master');
            $forumify->setActive(false);


            $this->pluginRepository->save($forumify);
            $plugin = $forumify;
        }

        $permissions = $this->forumifyBundle->getPluginPermissions()->getPermissions();

        foreach ($permissions as $forumifyPermission) {
            $existingPermission = $this->permissionRepository->findOneBy(['plugin' => $plugin->getId(), 'permission' => $forumifyPermission]);
            if ($existingPermission === null) {
                $permission = new Permission();
                $permission->setPlugin($plugin);
                $permission->setPermission($forumifyPermission);

                $this->permissionRepository->save($permission);
            }
        }
    }

}