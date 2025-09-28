<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Service\TemplateNamespace;
use Forumify\Core\Service\ThemeTemplateService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Twig\Environment;

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

    #[LiveProp]
    public Theme $theme;

    public function __construct(
        private readonly ThemeTemplateService $themeTemplateService,
        private readonly Environment $twig,
        private readonly SluggerInterface $slugger = new AsciiSlugger(),
        private readonly Filesystem $fs = new Filesystem(),
    ) {
    }

    #[LiveAction]
    public function cd(#[LiveArg] string $cd): void
    {
        if ($cd === '..') {
            $firstDs = strpos($this->cwd, DIRECTORY_SEPARATOR);
            $this->cwd = $firstDs ? substr($this->cwd, 0, $firstDs) : '';
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

        $localPath = Path::join(
            $this->themeTemplateService->getThemeOverrideDir($this->theme),
            $namespace,
            ...$path,
        );
        if (file_exists($localPath)) {
            return;
        }

        $this->fs->dumpFile($localPath, $this->getOverrideDefaultContent($this->openFile));
        $this->twig->removeCache($this->openFile);
    }

    #[LiveAction]
    public function saveContent(#[LiveArg] ?string $content = null): void
    {
        if ($content === null) {
            return;
        }

        $path = explode(DIRECTORY_SEPARATOR, $this->openFile);
        $namespace = array_shift($path);
        if (str_starts_with($namespace, '@')) {
            $namespace = substr($namespace, 1);
        }

        $localPath = Path::join(
            $this->themeTemplateService->getThemeOverrideDir($this->theme),
            $namespace,
            ...$path
        );

        $this->fs->dumpFile($localPath, $content);
        $this->twig->removeCache($this->openFile);
    }

    #[LiveAction]
    public function deleteOpenFile(): void
    {
        $path = explode(DIRECTORY_SEPARATOR, $this->openFile);
        $currentNamespace = array_shift($path);
        if (str_starts_with($currentNamespace, '@')) {
            $currentNamespace = substr($currentNamespace, 1);
        }

        $path = substr($this->openFile, strpos($this->openFile, DIRECTORY_SEPARATOR));
        $realPath = Path::join(
            $this->themeTemplateService->getThemeOverrideDir($this->theme),
            $currentNamespace,
            $path
        );
        if (file_exists($realPath)) {
            @unlink($realPath);
            $this->twig->removeCache($this->openFile);
        }
    }

    /**
     * @return array<array{file: string, overridden: bool, directory: bool}>
     */
    public function ls(): array
    {
        $namespaces = $this->themeTemplateService->getNamespaces($this->theme);
        if (empty($this->cwd)) {
            $roots = array_filter($namespaces, fn (TemplateNamespace $namespace) => (
                $namespace->getName() !== 'Local'
                && is_dir($namespace->getRoot())
            ));
            return array_map(fn (TemplateNamespace $namespace) => ([
                'file' => '@' . $namespace->getName(),
                'overridden' => false,
                'directory' => true,
            ]), $roots);
        }

        $path = explode('/', $this->cwd);
        $namespace = array_shift($path);

        $rootDir = $namespaces[$namespace]->getRoot();
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

        $namespaces = $this->themeTemplateService->getNamespaces($this->theme);
        $namespace = $namespaces[$currentNamespace];
        $file = Path::join($namespace->getRoot(), $path);
        $contents[] = [
            'key' => $this->slugger->slug($currentNamespace . '-' . $fileKey)->toString(),
            'namespace' => $currentNamespace,
            'location' => $file,
            'content' => is_file($file) ? file_get_contents($file) : null,
            'defaultContent' => $this->getOverrideDefaultContent($fileToCheck),
            'readonly' => true,
        ];

        foreach ($namespaces as $name => $overrideNamespace) {
            $file = Path::join($overrideNamespace->getOverrideRoot(), $namespace->getName(), $path);
            $fileExists = is_file($file);
            if ($name !== 'Local' && !$fileExists) {
                continue;
            }

            $contents[] = [
                'key' => $this->slugger->slug($name . '-' . $fileKey)->toString(),
                'namespace' => $name,
                'location' => $file,
                'content' => $fileExists ? file_get_contents($file) : null,
                'defaultContent' => $this->getOverrideDefaultContent($fileToCheck),
                'readonly' => $name !== 'Local',
            ];
        }
        return $contents;
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
