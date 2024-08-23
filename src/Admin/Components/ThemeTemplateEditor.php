<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Core\Service\ThemeService;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    'ThemeTemplateEditor',
    '@Forumify/admin/components/theme_template_editor/theme_template_editor.html.twig'
)]
class ThemeTemplateEditor
{
    private const DIRECTORY_CACHE = 'forumify.theme_template_editor.directory_cache';

    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public Theme $theme;

    #[LiveProp(writable: true)]
    public array $openFiles = [];

    public function __construct(
        private readonly ThemeService $themeService,
        private readonly PluginRepository $pluginRepository,
        private readonly CacheInterface $cache,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $rootDir,
    ) {
    }

    public function getDirs(): array
    {
        return $this->cache->get(self::DIRECTORY_CACHE, $this->refreshDirectoryCache(...));
    }

    #[LiveAction]
    public function openTemplate(#[LiveArg] array $data): void
    {
        $id = (new AsciiSlugger())->slug(uniqid($data['name'], false))->toString();

        $data['id'] = $id;
        foreach ($data['locations'] as $location => $file) {
            $data['locations'][$location] = file_get_contents($file);
        }
        if (!isset($data['locations']['local'])) {
            $data['locations']['local'] = null;
        }

        $this->openFiles[$id] = $data;
    }

    #[LiveAction]
    public function closeTemplate(#[LiveArg] string $id): void
    {
        unset($this->openFiles[$id]);
    }

    #[LiveAction]
    public function overrideTemplate(#[LiveArg] string $id): void
    {
        $path = $this->openFiles[$id]['path'];
        [$localDir] = $this->themeService->getTemplateDirectories($this->theme);
        if (str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }

        file_put_contents("$localDir/$path", "{% extends '@!$path' %}\n");
        $this->cache->delete(self::DIRECTORY_CACHE);

        $this->openTemplate($this->openFiles[$id]);

//        $this->dispatchBrowserEvent('theme-template-editor:reset-editor', [
//            'fileId' => $id,
//            'location' => 'local',
//        ]);
    }

    #[LiveAction]
    public function deleteOverride(#[LiveArg] string $id): void
    {
        $path = $this->openFiles[$id]['path'];
        [$localDir] = $this->themeService->getTemplateDirectories($this->theme);
        if (str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }

        @unlink("$localDir/$path");
        $this->cache->delete(self::DIRECTORY_CACHE);
    }

    private function refreshDirectoryCache(): array
    {
        $directory = [];

        $forumifyRoot = "{$this->rootDir}/vendor/forumify/forumify-platform/templates";
        $directory['Forumify'] = [
            'name' => 'Forumify',
            'locations' => ['platform' => $forumifyRoot],
            'children' => [],
        ];
        $this->insertDirectories($directory['Forumify']['children'], $forumifyRoot, 'platform', 'Forumify');

        /** @var Plugin $plugin */
        foreach ($this->pluginRepository->findBy(['type' => 'plugin']) as $plugin) {
            $name = last(explode('\\', $plugin->getPluginClass()));
            $pluginRoot = "{$this->rootDir}/vendor/{$plugin->getPackage()}/templates";

            $directory[$name] = [
                'name' => $name,
                'locations' => ['plugin' => $pluginRoot],
                'children' => [],
            ];
            $this->insertDirectories($directory[$name]['children'], $pluginRoot, 'plugin', $name);
        }

        [$localDir, $themeDir] = $this->themeService->getTemplateDirectories($this->theme);
        $this->insertDirectories($directory, $themeDir, 'theme', '');
        $this->insertDirectories($directory, $localDir, 'local', '');

        $this->sortDirectory($directory);

        return $directory;
    }

    private function insertDirectories(array &$dirs, string $root, string $location, string $path): void
    {
        foreach (scandir($root, SCANDIR_SORT_NONE) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $absolute = "$root/$file";
            $relative = "$path/$file";
            $isDir = is_dir($absolute);

            if (!isset($dirs[$file])) {
                $dirs[$file] = [
                    'name' => $file,
                    'path' => $relative,
                ];
            }

            $dirs[$file]['locations'][$location] = $absolute;

            if ($isDir) {
                if (!isset($dirs[$file]['children'])) {
                    $dirs[$file]['children'] = [];
                }
                $this->insertDirectories($dirs[$file]['children'], $absolute, $location, $relative);
            }
        }
    }

    private function sortDirectory(array &$directory): void
    {
        uasort($directory, $this->compare(...));

        foreach ($directory as &$dir) {
            if (isset($dir['children'])) {
                $this->sortDirectory($dir['children']);
            }
        }
    }

    private function compare(array $a, array $b): int
    {
        $ptA = isset($a['children']) ? 0 : 100;
        $ptB = isset($b['children']) ? 0 : 100;

        return $ptA - $ptB + strcmp($a['name'], $b['name']);
    }
}
