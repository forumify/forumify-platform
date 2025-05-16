<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Repository\PluginRepository;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
}
