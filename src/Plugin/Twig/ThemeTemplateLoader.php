<?php

declare(strict_types=1);

namespace Forumify\Plugin\Twig;

use Forumify\Core\Service\ThemeService;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Twig\Loader\LoaderInterface;
use Twig\Source;

#[AutoconfigureTag('twig.loader', ['priority' => 100])]
class ThemeTemplateLoader implements LoaderInterface
{
    private array $cache = [];

    public function __construct(
        private readonly ThemeService $themeService,
    ) {
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
            // If the name starts with !, the original template is requested
            // This is to prevent infinite looping where a template with the same name extends the original
            return null;
        }

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        try {
            [$localDir, $themeDir] = $this->themeService->getTemplateDirectories();
        } catch (RuntimeException) {
            return null;
        }

        $localFile = "$localDir/$name";
        if (is_file($localFile)) {
            $this->cache[$name] = $localFile;
            return $localFile;
        }

        $themeFile = "$themeDir/$name";
        if (is_file($themeFile)) {
            $this->cache[$name] = $themeFile;
            return $themeFile;
        }

        $this->cache[$name] = null;
        return null;
    }
}
