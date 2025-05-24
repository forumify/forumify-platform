<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Contracts\Cache\CacheInterface;

class ThemeTemplateService
{
    public const CACHE_KEY = 'forumify.theme.template_locations';
    private ?array $locations = null;

    public function __construct(
        private readonly ThemeRepository $themeRepository,
        private readonly PluginRepository $pluginRepository,
        private readonly CacheInterface $cache,
        #[Autowire('%twig.default_path%')]
        private readonly string $twigPath,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $rootDir,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getTemplateLocations(): array
    {
        if ($this->locations !== null) {
            return $this->locations;
        }

        $this->locations = $this->cache->get(self::CACHE_KEY, function () {
            $locations = [];

            $activePlugins = $this->pluginRepository->findBy(['type' => Plugin::TYPE_PLUGIN, 'active' => true]);
            foreach ($activePlugins as $plugin) {
                $pluginPackage = $plugin->getPackage();
                $locations[] = "{$this->rootDir}/vendor/$pluginPackage/templates/bundles";
            }

            $activeTheme = $this->themeRepository->findOneBy(['active' => true]);
            if ($activeTheme !== null) {
                $themePackage = $activeTheme->getPlugin()->getPackage();

                $locations[] = "{$this->rootDir}/vendor/$themePackage/templates";
                $locations[] = "{$this->twigPath}/themes/$themePackage";
            }

            return $locations;
        });
        return $this->locations;
    }

    /**
     * @return array<TemplateNamespace>
     */
    public function getNamespaces(Theme $theme): array
    {
        $namespaces = [
            '@Forumify' => new TemplateNamespace(
                'Forumify',
                "{$this->rootDir}/vendor/forumify/forumify-platform/templates",
                "{$this->rootDir}/vendor/forumify/forumify-platform/templates/bundles",
            ),
        ];

        $plugins = $this->pluginRepository->findActivePlugins();
        $plugins[] = $theme->getPlugin();

        foreach ($this->pluginRepository->findActivePlugins() as $plugin) {
            $namespace = TemplateNamespace::fromPlugin($this->rootDir, $plugin);
            $namespaces['@' . $namespace->getName()] = $namespace;
        }

        $themeOverrideDir = $this->getThemeOverrideDir($theme);
        $namespaces['Local'] = new TemplateNamespace(
            'Local',
            $themeOverrideDir,
            $themeOverrideDir
        );

        return $namespaces;
    }

    public function getThemeOverrideDir(Theme $theme): string
    {
        return Path::join($this->twigPath, 'themes', $theme->getPlugin()->getPackage());
    }
}
