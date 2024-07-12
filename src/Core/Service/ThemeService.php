<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Plugin\AbstractForumifyTheme;
use League\Flysystem\FilesystemOperator;
use Symfony\Contracts\Cache\CacheInterface;

class ThemeService
{
    public const THEME_LAST_MODIFIED_CACHE_KEY = 'forumify.theme.last_modified';
    public const CURRENT_THEME_COOKIE = 'forumify_theme';
    public const THEME_FILE_FORMAT = 'themes/%s-%s.css';

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly ThemeRepository $themeRepository,
        private readonly FilesystemOperator $assetStorage,
    ) {
    }

    public function clearCache(): void
    {
        $this->cache->delete(self::THEME_LAST_MODIFIED_CACHE_KEY);
    }

    public function getThemeMetaData(): array
    {
        return $this->cache->get(self::THEME_LAST_MODIFIED_CACHE_KEY, function (): array {
            /** @var Theme|null $theme */
            $theme = $this->themeRepository->findOneBy(['active' => true]);
            if ($theme === null) {
                return [];
            }

            $modifiedTimestamp = $theme->getUpdatedAt()?->getTimestamp() ?? 0;
            $modifiedTimestamp = (string)$modifiedTimestamp;
            $this->dumpStyleSheets($theme, $modifiedTimestamp);

            $metaData = [
                'lastModified' => $modifiedTimestamp,
            ];

            $plugin = $theme->getPlugin();
            $instance = $plugin->getPlugin();
            if ($instance instanceof AbstractForumifyTheme) {
                $metaData['stylesheets'] = [];
                foreach ($instance->getStylesheets() as $stylesheet) {
                    $metaData['stylesheets'][] = $plugin->getPackage() . '/' . $stylesheet;
                }
            }

            return $metaData;
        });
    }

    private function dumpStyleSheets(Theme $theme, string $key): void
    {
        $plugin = $theme->getPlugin()->getPlugin();
        if (!$plugin instanceof AbstractForumifyTheme) {
            return;
        }

        $css = $theme->getCss();
        ['default' => $defaultVars, 'dark' => $darkVars] = $this->dumpThemeVars($plugin, $theme);

        if ($this->assetStorage->directoryExists('themes')) {
            $this->assetStorage->deleteDirectory('themes');
        }
        $this->assetStorage->createDirectory('themes');

        $system = ":root{{$defaultVars}}@media (prefers-color-scheme: dark) {:root{{$darkVars}}}" . $css;
        $this->assetStorage->write(sprintf(self::THEME_FILE_FORMAT, 'system', $key), $system);

        $default = ":root{{$defaultVars}}" . $css;
        $this->assetStorage->write(sprintf(self::THEME_FILE_FORMAT, 'default', $key), $default);

        $defaultVars .= $darkVars;
        $dark = ":root{{$defaultVars}}" . $css;
        $this->assetStorage->write(sprintf(self::THEME_FILE_FORMAT, 'dark', $key), $dark);
    }

    private function dumpThemeVars(AbstractForumifyTheme $plugin, Theme $theme): array
    {
        $config = ['default' => [], 'dark' => []];
        foreach ($plugin->getThemeConfig()->vars as $var) {
            $config['default'][$var->key] = $var->defaultValue;
            $config['dark'][$var->key] = $var->defaultDarkValue ?: $var->defaultValue;
        }

        $overrideConfig = $theme->getThemeConfig();
        $vars = [
            'default' => array_filter(array_merge($config['default'], array_filter($overrideConfig['default'] ?? []))),
            'dark' => array_filter(array_merge($config['dark'], array_filter($overrideConfig['dark'] ?? []))),
        ];

        return [
            'default' => $this->parseThemeVars($vars['default']),
            'dark' => $this->parseThemeVars($vars['dark']),
        ];
    }

    private function parseThemeVars(array $config): string
    {
        $css = '';
        foreach ($config as $key => $value) {
            $css .= "--$key:$value;";
        }
        return $css;
    }
}
