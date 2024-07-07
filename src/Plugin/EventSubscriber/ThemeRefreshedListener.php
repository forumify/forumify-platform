<?php

declare(strict_types=1);

namespace Forumify\Plugin\EventSubscriber;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Plugin\Entity\Plugin;
use Forumify\Plugin\Event\PluginRefreshedEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

#[AsEventListener]
class ThemeRefreshedListener
{
    public function __construct(
        private readonly ThemeRepository $themeRepository,
        private readonly Filesystem $filesystem,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
    ) {
    }

    public function __invoke(PluginRefreshedEvent $event): void
    {
        if ($event->plugin->getType() !== Plugin::TYPE_THEME) {
            return;
        }

        $plugin = $event->plugin;
        $theme = $this->themeRepository->findOneBy(['plugin' => $plugin]);
        if ($theme === null) {
            $theme = $this->createTheme($plugin);
        }

        try {
            $this->copyAssets($theme);
        } catch (IOException) {
            // ok
        }
    }

    private function createTheme(Plugin $plugin): Theme
    {
        $theme = new Theme();
        $theme->setName($plugin->getPlugin()->getPluginMetadata()->name);
        $theme->setPlugin($plugin);
        if ($plugin->getPackage() === 'forumify/forumify-theme') {
            $theme->setActive(true);
        }

        $this->themeRepository->save($theme);
        return $theme;
    }

    private function copyAssets(Theme $theme): void
    {
        $package = $theme->getPlugin()->getPackage();

        $originDir = implode(DIRECTORY_SEPARATOR, [$this->projectDir, 'vendor', $package, 'public']);
        if (!is_dir($originDir)) {
            return;
        }

        $targetDir = implode(DIRECTORY_SEPARATOR, [$this->projectDir, 'public', 'themes', $package]);
        if (is_dir($targetDir)) {
            $this->filesystem->remove($targetDir);
        }

        $this->filesystem->mkdir($targetDir);
        $this->filesystem->mirror($originDir, $targetDir);
    }
}
