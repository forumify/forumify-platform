<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Core\Repository\ThemeRepository;
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

        $originalTemplate = "@!$namespace/" . implode('/', $path);
        $content = "{% extends '$originalTemplate' %}";
        $this->fs->dumpFile($localPath, $content);
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

    public function ls(): array
    {
        if (empty($this->cwd)) {
            // TODO: list all plugins too
            return [['file' => '@Forumify', 'directory' => true]];
        }

        $path = explode('/', $this->cwd);
        array_shift($path);

        $rootDir = "{$this->rootDir}/vendor/forumify/forumify-platform/templates";
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
        $locations = $this->getFileLocations();
        $path = substr($this->openFile, strpos($this->openFile, DIRECTORY_SEPARATOR));
        $fileKey = u(pathinfo($path, PATHINFO_FILENAME))->replace('.', '-')->toString();

        $contents = [];
        foreach ($locations as $location) {
            $location['key'] .= '-' . $fileKey;
            $file = $location['location'] . $path;
            if (is_file($file)) {
                $location['content'] = file_get_contents($file);
                $contents[] = $location;
            }
        }
        return $contents;
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
}
