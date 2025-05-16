<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function Symfony\Component\String\u;

#[AsLiveComponent(
    'Forumify\\Admin\\TemplateEditor',
    '@Forumify/admin/components/template_editor/template_editor.html.twig',
)]
class TemplateEditor
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $cwd = '';

    #[LiveProp(writable: true)]
    public ?string $openFile = null;

    #[LiveProp(writable: true)]
    public ?string $openFileContent = null;

    #[LiveProp]
    public Theme $theme;

    private readonly string $localPath;
    private readonly Filesystem $fs;

    public function __construct(
        private readonly PluginRepository $pluginRepository,
        private readonly ThemeRepository $themeRepository,
        #[Autowire('%twig.default_path%')]
        private readonly string $twigPath,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $rootDir,
    ) {
        $this->fs = new Filesystem();
    }

    #[LiveAction]
    public function cd(#[LiveArg] string $cd): void
    {
        if ($cd === '..') {
            $path = explode(DIRECTORY_SEPARATOR, $this->cwd);
            array_pop($path);
            $this->cwd = implode(DIRECTORY_SEPARATOR, $path);
            return;
        }

        $this->cwd .= empty($this->cwd) ? $cd : (DIRECTORY_SEPARATOR . $cd);
    }

    #[LiveAction]
    public function selectFile(#[LiveArg] string $file): void
    {
        $this->openFile = $this->cwd . DIRECTORY_SEPARATOR . $file;
    }

    #[LiveAction]
    public function overrideOpenFile(): void
    {
        $path = explode(DIRECTORY_SEPARATOR, $this->openFile);
        $namespace = array_shift($path);
        if (str_starts_with($namespace, '@')) {
            $namespace = substr($namespace, 1);
        }

        $localPath = Path::join($this->getThemeOverrideDir(), $namespace, ...$path);
        if (file_exists($localPath)) {
            return;
        }

        $this->fs->dumpFile($localPath, $this->getOverrideDefaultContent());
    }

    private function getOverrideDefaultContent(): string
    {
        $path = explode(DIRECTORY_SEPARATOR, $this->openFile);
        $namespace = array_shift($path);
        if (str_starts_with($namespace, '@')) {
            $namespace = substr($namespace, 1);
        }

        $originalTemplate = "@!$namespace/" . implode('/', $path);
        return "{% extends '$originalTemplate' %}";
    }

    #[LiveAction]
    public function saveOpenFile(): void
    {
        $this->saveContent($this->openFileContent);
    }

    #[LiveAction]
    public function saveContent(#[LiveArg] string $content): void
    {
        $path = explode(DIRECTORY_SEPARATOR, $this->openFile);
        $namespace = array_shift($path);
        if (str_starts_with($namespace, '@')) {
            $namespace = substr($namespace, 1);
        }

        $localPath = Path::join($this->getThemeOverrideDir(), $namespace, ...$path);
        $this->fs->dumpFile($localPath, $content);
    }

    #[LiveAction]
    public function deleteOpenFile(): void
    {
        $path = substr($this->openFile, strpos($this->openFile, DIRECTORY_SEPARATOR));
        $realPath = Path::join("{$this->getThemeOverrideDir()}/Forumify", $path);
        if (file_exists($realPath)) {
            @unlink($realPath);
        }
    }

    public function ls(): array
    {
        $namespaces = $this->getNamespaces();
        if (empty($this->cwd)) {
            return array_filter($namespaces, fn ($namespace) => !empty($namespace['root']) && is_dir(($namespace['root'])));
        }

        $path = explode('/', $this->cwd);
        $namespace = array_shift($path);

        $rootDir = $namespaces[$namespace]['root'];
        $dir = $rootDir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $path);

        $files = [];
        if (is_dir($dir)) {
            foreach (@scandir($dir, SCANDIR_SORT_NONE) as $file) {
                if ($file !== '.') {
                    $files[] = [
                        'file' => $file,
                        'directory' => is_dir($dir . DIRECTORY_SEPARATOR . $file),
                    ];
                }
            }
        }

        uasort($files, function (array $a, array $b) {
            $aIsDir = $a['directory'];
            $bIsDir = $b['directory'];

            if ($aIsDir && !$bIsDir) {
                return -1;
            }

            if (!$aIsDir && $bIsDir) {
                return 1;
            }

            return strcmp($a['file'], $b['file']);
        });

        return $files;
    }

    public function getFileContents(): array
    {
        $path = explode('/', $this->openFile);
        $currentNamespace = array_shift($path);
        $path = implode(DIRECTORY_SEPARATOR, $path);
        $fileKey = u(pathinfo($path, PATHINFO_FILENAME))->replace('.', '-')->toString();

        $contents = [];

        $namespaces = $this->getNamespaces();
        $namespace = $namespaces[$currentNamespace];
        $file = Path::join($namespace['root'], $path);
        $contents[] = [
            'key' => strtolower($currentNamespace) . '-' . $fileKey,
            'namespace' => $currentNamespace,
            'location' => $file,
            'content' => is_file($file) ? file_get_contents($file) : null,
            'defaultContent' => $this->getOverrideDefaultContent(),
            'readonly' => true,
        ];

        foreach ($namespaces as $currentNamespace => $overrideNamespace) {
            $file = Path::join($overrideNamespace['overrideRoot'], $path);
            if (!is_file($file)) {
                continue;
            }

            $contents[] = [
                'key' => strtolower($currentNamespace) . '-' . $fileKey,
                'namespace' => $currentNamespace,
                'location' => $file,
                'content' => is_file($file) ? file_get_contents($file) : null,
                'defaultContent' => $this->getOverrideDefaultContent(),
                'readonly' => $currentNamespace !== 'Local',
            ];
        }
        return $contents;

        $locations = $this->getFileLocations();
        $path = substr($this->openFile, strpos($this->openFile, DIRECTORY_SEPARATOR));
        $fileKey = u(pathinfo($path, PATHINFO_FILENAME))->replace('.', '-')->toString();

        $contents = [];
        foreach ($locations as $location) {
            $location['key'] .= '-' . $fileKey;
            $file = $location['location'] . $path;
            $location['content'] = is_file($file) ? file_get_contents($file) : null;
            $location['defaultContent'] = $this->getOverrideDefaultContent();
            $contents[] = $location;
        }
        return $contents;
    }

    private function getNamespaces(): array
    {
        $namespaces = ['@Forumify' => [
            'file' => '@Forumify',
            'directory' => true,
            'root' => "{$this->rootDir}/vendor/forumify/forumify-platform/templates",
            'overrideRoot' => "{$this->rootDir}/vendor/forumify/forumify-platform/templates/bundles",
        ]];
        foreach ($this->pluginRepository->findActivePlugins() as $plugin) {
            $namespace = '@' . $this->pluginToNamespace($plugin);
            $namespaces[$namespace] = [
                'file' => $namespace,
                'directory' => true,
                'root' => "{$this->rootDir}/vendor/{$plugin->getPackage()}/templates",
                'overrideRoot' => "{$this->rootDir}/vendor/{$plugin->getPackage()}/templates/bundles",
            ];
        }

        $namespace = '@' . $this->pluginToNamespace($this->theme->getPlugin());
        $namespaces[$namespace] = [
            'file' => $namespace,
            'directory' => true,
            'root' => "{$this->rootDir}/vendor/{$this->theme->getPlugin()->getPackage()}/templates",
            'overrideRoot' => "{$this->rootDir}/vendor/{$this->theme->getPlugin()->getPackage()}/templates"
        ];

        $namespaces['Local'] = [
            'file' => 'Local',
            'directory' => true,
            'overrideRoot' => $this->getThemeOverrideDir(),
        ];

        return $namespaces;
    }

    private function getFileLocations(): array
    {
        $locations = [
            [
                'key' => 'forumify',
                'namespace' => '@Forumify',
                'location' => "{$this->rootDir}/vendor/forumify/forumify-platform/templates",
                'readonly' => true,
            ],
            [
                'key' => 'local',
                'namespace' => 'Local',
                'location' => "{$this->getThemeOverrideDir()}/Forumify",
                'readonly' => false,
            ]
        ];

        return $locations;
    }

    private function getThemeOverrideDir(): string
    {
        return Path::join($this->twigPath, 'themes', $this->theme->getPlugin()->getPackage());
    }

    private function pluginToNamespace(Plugin $plugin): string
    {
        $class = $plugin->getPluginClass();
        return substr($class, strrpos($class, '\\') + 1);
    }
}
