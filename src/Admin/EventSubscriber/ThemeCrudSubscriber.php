<?php

declare(strict_types=1);

namespace Forumify\Admin\EventSubscriber;

use Forumify\Admin\Crud\Event\PostSaveCrudEvent;
use Forumify\Core\Entity\Theme;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Core\Service\ThemeService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThemeCrudSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ThemeRepository $themeRepository,
        private readonly ThemeService $themeService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostSaveCrudEvent::getName(Theme::class) => 'postThemeSave',
        ];
    }

    public function postThemeSave(PostSaveCrudEvent $event): void
    {
        /** @var Theme $theme */
        $theme = $event->getEntity();
        $this->ensureOnlyOneActive($theme);

        $this->themeService->clearCache();
    }

    private function ensureOnlyOneActive(Theme $theme): void
    {
        if (!$theme->isActive()) {
            return;
        }

        /** @var array<Theme> $activeThemes */
        $activeThemes = $this->themeRepository->findBy(['active' => true]);
        foreach ($activeThemes as $activeTheme) {
            if ($theme->getId() === $activeTheme->getId()) {
                continue;
            }
            $activeTheme->setActive(false);
            $this->themeRepository->save($activeTheme);
        }
    }
}
