<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Plugin\AbstractForumifyTheme;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

/**
 * @phpstan-type ThemeMetaData array{
 *     themeId?: int,
 *     pluginPackage?: string,
 *     lastModified?: string,
 *     stylesheets?: string[],
 * }
 */
class ThemeService
{
    public const THEME_LAST_MODIFIED_CACHE_KEY = 'forumify.theme.last_modified';
    public const CURRENT_THEME_COOKIE = 'forumify_theme';
    public const THEME_FILE_FORMAT = 'themes/%s-%s.css';

    /** @var ThemeMetaData|null */
    private ?array $themeMetadata = null;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly ThemeRepository $themeRepository,
        private readonly FilesystemOperator $assetStorage,
        private readonly Environment $twig,
    ) {
    }

    public function clearCache(): void
    {
        try {
            $this->cache->delete(self::THEME_LAST_MODIFIED_CACHE_KEY);
            $this->cache->delete(ThemeTemplateService::CACHE_KEY);
        } catch (InvalidArgumentException) {
        }
        $this->themeMetadata = null;
    }

    /**
     * @return ThemeMetaData
     */
    public function getThemeMetaData(): array
    {
        if ($this->themeMetadata !== null) {
            return $this->themeMetadata;
        }

        $this->themeMetadata = $this->cache->get(self::THEME_LAST_MODIFIED_CACHE_KEY, function (): array {
            /** @var Theme|null $theme */
            $theme = $this->themeRepository->findOneBy(['active' => true]);
            if ($theme === null) {
                return [];
            }

            $modifiedTimestamp = $theme->getUpdatedAt()?->getTimestamp() ?? 0;
            $modifiedTimestamp = (string)$modifiedTimestamp;
            $this->dumpStyleSheets($theme, $modifiedTimestamp);

            $metaData = [
                'themeId' => $theme->getId(),
                'pluginPackage' => $theme->getPlugin()->getPackage(),
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
        return $this->themeMetadata;
    }

    private function dumpStyleSheets(Theme $theme, string $key): void
    {
        $plugin = $theme->getPlugin()->getPlugin();
        if (!$plugin instanceof AbstractForumifyTheme) {
            return;
        }

        ['default' => $defaultVars, 'dark' => $darkVars] = $this->dumpThemeVars($plugin, $theme);

        try {
            if ($this->assetStorage->directoryExists('themes')) {
                $this->assetStorage->deleteDirectory('themes');
            }

            $this->assetStorage->createDirectory('themes');

            $themeCss = $this->twig->createTemplate($theme->getCss());
            $this->assetStorage->write(sprintf(self::THEME_FILE_FORMAT, 'custom', $key), $themeCss->render());

            $system = ":root{{$defaultVars}}@media (prefers-color-scheme: dark) {:root{{$darkVars}}}";
            $this->assetStorage->write(sprintf(self::THEME_FILE_FORMAT, 'system', $key), $system);

            $default = ":root{{$defaultVars}}";
            $this->assetStorage->write(sprintf(self::THEME_FILE_FORMAT, 'default', $key), $default);

            $defaultVars .= $darkVars;
            $dark = ":root{{$defaultVars}}";
            $this->assetStorage->write(sprintf(self::THEME_FILE_FORMAT, 'dark', $key), $dark);
        } catch (FilesystemException|LoaderError|SyntaxError) {
        }
    }

    /**
     * @return array{default: string, dark: string}
     */
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

    /**
     * @param array<string, string> $config
     */
    private function parseThemeVars(array $config): string
    {
        $css = '';
        foreach ($config as $key => $value) {
            $css .= "--$key:$value;";
        }
        return $css;
    }
}
