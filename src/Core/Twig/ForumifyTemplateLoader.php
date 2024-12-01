<?php

declare(strict_types=1);

namespace Forumify\Core\Twig;

use Forumify\Core\Repository\PluginRepository;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * This loader builds an inheritance extension list in var/cache/templates.
 * First the plugins are loaded, then the theme, then local theme overrides and finally the original.
 */
#[AutoconfigureTag('twig.loader', ['priority' => 100])]
class ForumifyTemplateLoader implements LoaderInterface
{
    private readonly string $cacheDir;
    /** @var array<string,string> */
    private array $cache = [];

    public function __construct(
        private readonly ThemeRepository $themeRepository,
        private readonly PluginRepository $pluginRepository,
        #[Autowire('%twig.default_path%')]
        private readonly string $twigPath,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $rootDir,
        #[Autowire('%kernel.cache_dir%')]
        string $cacheDir,
    ) {
        $this->cacheDir = $cacheDir . '/templates';
    }

    public function getSourceContext(string $name): Source
    {
        $path = $this->findTemplate($name);
        if ($path === null) {
            return new Source('', $name, '');
        }

        return new Source(file_get_contents($path), $name, $path);
    }

    public function getCacheKey(string $name): string
    {
        $path = $this->findTemplate($name);
        return $path ?? '';
    }

    public function isFresh(string $name, int $time): bool
    {
        $path = $this->findTemplate($name);
        if ($path === null) {
            return true;
        }

        return filemtime($path) < $time;
    }

    public function exists(string $name): bool
    {
        return $this->findTemplate($name) !== null;
    }

    private function findTemplate(string $name): ?string
    {
        if (str_starts_with($name, '@')) {
            $name = substr($name, 1);
        }

        if (str_starts_with($name, '!')) {
            // The original template is requested, fallback to default loader
            return null;
        }

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (str_starts_with($name, '~')) {
            // A previously cached template is requested, look inside cache dir
            return $this->loadFromTemplateCache($name);
        }

        return $this->loadTemplateInheritance($name);
    }

    private function loadFromTemplateCache(string $name): ?string
    {
        $name = substr($name, 1);
        $template = "$this->cacheDir/$name";
        if (is_file($template)) {
            $this->cache[$name] = $template;
            return $template;
        }
        $this->cache[$name] = null;
        return null;
    }

    private function loadTemplateInheritance(string $name): ?string
    {
        $locations = array_map(static fn ($location) => "$location/$name", $this->getTemplateLocations());
        $existingTemplates = array_filter($locations, 'is_file');

        $i = 0;
        $entryPoint = null;
        foreach ($existingTemplates as $template) {
            $contents = file_get_contents($template);
            if (empty($contents)) {
                continue;
            }

            $firstLineBreak = strpos($contents, PHP_EOL);
            $firstLine = substr($contents, 0, $firstLineBreak);
            if (str_contains($firstLine, 'extends')) {
                $contents = substr($contents, $firstLineBreak + 1);
            }

            if ($i > 0) {
                $prev = $i - 1;
                $contents = "{% extends '~$name/$prev.html.twig' %}" . PHP_EOL . $contents;
            } else {
                $contents = "{% extends '@!$name' %}" . PHP_EOL . $contents;
            }

            if (!is_dir("$this->cacheDir/$name")) {
                (new Filesystem())->mkdir("$this->cacheDir/$name");
            }

            $entryPoint = "$this->cacheDir/$name/$i.html.twig";
            file_put_contents($entryPoint, $contents);
            $i++;
        }

        $this->cache[$name] = $entryPoint;
        return $entryPoint;
    }

    /**
     * @return array<string>
     */
    private function getTemplateLocations(): array
    {
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
    }
}
