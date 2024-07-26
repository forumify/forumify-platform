<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\RuntimeExtensionInterface;

abstract class MenuManager implements RuntimeExtensionInterface
{
    /** @var array<MenuBuilderInterface> */
    protected array $menuBuilders;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(iterable $menuBuilders, AuthorizationCheckerInterface $authorizationChecker)
    {
        foreach ($menuBuilders as $menuBuilder) {
            if ($menuBuilder instanceof MenuBuilderInterface) {
                $this->menuBuilders[] = $menuBuilder;
            }
        }
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getMenu(): Menu
    {
        $menu = new Menu();
        foreach ($this->menuBuilders as $menuBuilder) {
            $menuBuilder->build($menu);
        }

        return $this->filterMenuByPermissions($menu);
    }

    private function filterMenuByPermissions(Menu $menu): Menu
    {
        $filteredMenu = new Menu();
        foreach ($menu->getEntries() as $item) {
            if ($this->hasPermission($item)) {
                $filteredMenu->addItem($item);
            }
        }

        return $filteredMenu;
    }

    private function hasPermission(Menu|MenuItem $item): bool
    {
        $permission = $item->getPermission();

        if ($permission === null) {
            return true;
        }

        return $this->authorizationChecker->isGranted($permission);
    }
}
