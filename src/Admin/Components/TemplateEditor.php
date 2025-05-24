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
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

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

    public function __construct(
        private readonly PluginRepository $pluginRepository,
        private readonly ThemeRepository $themeRepository,
        #[Autowire('%twig.default_path%')]
        private readonly string $twigPath,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $rootDir,
        private readonly SluggerInterface $slugger = new AsciiSlugger(),
        private readonly Filesystem $fs = new Filesystem(),
    ) {
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

        $this->fs->dumpFile($localPath, $this->getOverrideDefaultContent($this->openFile));
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
        // TODO: this doesn't work for plugins yet
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
            $roots = array_filter($namespaces, fn ($namespace) => (
                $namespace['namespace'] !== 'Local'
                && !empty($namespace['root'])
                && is_dir($namespace['root'])
            ));
            return array_map(fn ($root) => ([...$root, 'overridden' => false]), $roots);
        }

        $path = explode('/', $this->cwd);
        $namespace = array_shift($path);

        $rootDir = $namespaces[$namespace]['root'];
        $dir = Path::join($rootDir, ...$path);

        $files = [];
        if (is_dir($dir)) {
            foreach (@scandir($dir, SCANDIR_SORT_NONE) as $file) {
                if ($file === '.') {
                    continue;
                }

                $fileInfo = [
                    'file' => $file,
                    'directory' => is_dir(Path::join($dir, $file)),
                    'overridden' => false,
                ];

                $realLocation = Path::join($dir, $file);
                if (is_file($realLocation)) {
                    $locations = $this->getFileContents(Path::join($this->cwd, $file));
                    foreach ($locations as $location) {
                        if ($location['location'] !== $realLocation && !empty($location['content'])) {
                            $fileInfo['overridden'] = true;
                        }
                    }
                }

                $files[] = $fileInfo;
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

    public function getFileContents(?string $fileToCheck = null): array
    {
        $fileToCheck ??= $this->openFile;
        $path = explode(DIRECTORY_SEPARATOR, $fileToCheck);
        $currentNamespace = array_shift($path);
        $path = Path::join(...$path);
        $fileKey = pathinfo($path, PATHINFO_FILENAME);

        $contents = [];

        $namespaces = $this->getNamespaces();
        $namespace = $namespaces[$currentNamespace];
        $file = Path::join($namespace['root'], $path);
        $contents[] = [
            'key' => $this->slugger->slug($currentNamespace . '-' . $fileKey)->toString(),
            'namespace' => $currentNamespace,
            'location' => $file,
            'content' => is_file($file) ? file_get_contents($file) : null,
            'defaultContent' => $this->getOverrideDefaultContent($fileToCheck),
            'readonly' => true,
        ];

        foreach ($namespaces as $currentNamespace => $overrideNamespace) {
            $file = Path::join($overrideNamespace['overrideRoot'], $namespace['namespace'], $path);
            $fileExists = is_file($file);
            if ($currentNamespace !== 'Local' && !$fileExists) {
                continue;
            }

            $contents[] = [
                'key' => $this->slugger->slug($currentNamespace . '-' . $fileKey)->toString(),
                'namespace' => $currentNamespace,
                'location' => $file,
                'content' => $fileExists ? file_get_contents($file) : null,
                'defaultContent' => $this->getOverrideDefaultContent($fileToCheck),
                'readonly' => $currentNamespace !== 'Local',
            ];
        }
        return $contents;
    }

    /**
     * @return array<string, array<string, mixed>> List of namepaces sorted by priority
     *      1. Forumify
     *      2. Plugins
     *      3. Selected Theme
     *      4. Overrides on selected Theme
     */
    private function getNamespaces(): array
    {
        $namespaces = ['@Forumify' => [
            'file' => '@Forumify',
            'namespace' => 'Forumify',
            'directory' => true,
            'root' => "{$this->rootDir}/vendor/forumify/forumify-platform/templates",
            'overrideRoot' => "{$this->rootDir}/vendor/forumify/forumify-platform/templates/bundles",
        ]];

        foreach ($this->pluginRepository->findActivePlugins() as $plugin) {
            $this->insertPluginInNamespaces($plugin, $namespaces);
        }

        $this->insertPluginInNamespaces($this->theme->getPlugin(), $namespaces);

        $themeOverrideDir = $this->getThemeOverrideDir();
        $namespaces['Local'] = [
            'file' => 'Local',
            'namespace' => 'Local',
            'directory' => true,
            'root' => $themeOverrideDir,
            'overrideRoot' => $themeOverrideDir,
        ];

        return $namespaces;
    }

    private function insertPluginInNamespaces(Plugin $plugin, array &$namespaces): void
    {
        $namespace = $this->pluginToNamespace($plugin);
        $namespaces['@' . $namespace] = [
            'file' => '@' . $namespace,
            'namespace' => $namespace,
            'directory' => true,
            'root' => "{$this->rootDir}/vendor/{$plugin->getPackage()}/templates",
            'overrideRoot' => "{$this->rootDir}/vendor/{$plugin->getPackage()}/templates/bundles",
        ];
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

    private function getOverrideDefaultContent(string $file): string
    {
        $path = explode(DIRECTORY_SEPARATOR, $file);
        $namespace = array_shift($path);
        if (str_starts_with($namespace, '@')) {
            $namespace = substr($namespace, 1);
        }

        $originalTemplate = "@!$namespace/" . implode('/', $path);
        return "{% extends '$originalTemplate' %}";
    }
}
