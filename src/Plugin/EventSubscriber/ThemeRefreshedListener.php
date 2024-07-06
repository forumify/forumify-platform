<?php

declare(strict_types=1);

namespace Forumify\Plugin\EventSubscriber;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Plugin\Entity\Plugin;
use Forumify\Plugin\Event\PluginRefreshedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class ThemeRefreshedListener
{
    public function __construct(private readonly ThemeRepository $themeRepository)
    {
    }

    public function __invoke(PluginRefreshedEvent $event): void
    {
        if ($event->plugin->getType() !== Plugin::TYPE_THEME) {
            return;
        }

        $plugin = $event->plugin;
        $theme = $this->themeRepository->findOneBy(['plugin' => $plugin]);
        if ($theme !== null) {
            return;
        }

        $theme = new Theme();
        $theme->setName($plugin->getPlugin()->getPluginMetadata()->name);
        $theme->setPlugin($plugin);
        if ($plugin->getPackage() === 'forumify/forumify-theme') {
            $theme->setActive(true);
        }

        $this->themeRepository->save($theme);
    }
}
