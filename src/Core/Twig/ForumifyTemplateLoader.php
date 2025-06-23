<?php

declare(strict_types=1);

namespace Forumify\Core\Twig;

use Forumify\Core\Service\ThemeTemplateService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
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
    private Filesystem $fs;

    public function __construct(
        private readonly ThemeTemplateService $themeTemplateService,
        #[Autowire('%kernel.cache_dir%')]
        string $cacheDir,
    ) {
        $this->cacheDir = Path::join($cacheDir, 'templates');
        $this->fs = new Filesystem();
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
        $template = Path::join($this->cacheDir, $name);
        if (is_file($template)) {
            $this->cache[$name] = $template;
            return $template;
        }
        $this->cache[$name] = null;
        return null;
    }

    private function loadTemplateInheritance(string $name): ?string
    {
        $locations = array_map(
            static fn ($location) => Path::join($location, $name),
            $this->themeTemplateService->getTemplateLocations(),
        );
        $existingTemplates = array_filter($locations, 'is_file');

        $i = 0;
        $entryPoint = null;
        foreach ($existingTemplates as $template) {
            $contents = file_get_contents($template);
            if (empty($contents)) {
                continue;
            }

            $firstLineBreak = strpos($contents, PHP_EOL) ?: null;
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

            $entryPoint = Path::join($this->cacheDir, $name, "$i.html.twig");
            $this->fs->dumpFile($entryPoint, $contents);
            $i++;
        }

        $this->cache[$name] = $entryPoint;
        return $entryPoint;
    }
}
