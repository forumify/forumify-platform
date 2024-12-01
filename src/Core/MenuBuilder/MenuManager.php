<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

abstract class MenuManager implements RuntimeExtensionInterface
{
    /** @var array<MenuBuilderInterface> */
    protected array $menuBuilders;

    /**
     * @param iterable<MenuBuilderInterface|mixed> $menuBuilders
     */
    public function __construct(iterable $menuBuilders, private readonly Security $security)
    {
        foreach ($menuBuilders as $menuBuilder) {
            if ($menuBuilder instanceof MenuBuilderInterface) {
                $this->menuBuilders[] = $menuBuilder;
            }
        }
    }

    public function getMenu(): Menu
    {
        $menu = new Menu();
        foreach ($this->menuBuilders as $menuBuilder) {
            $menuBuilder->build($menu);
        }

        $this->filterMenuByPermissions($menu);
        return $menu;
    }

    private function filterMenuByPermissions(Menu $menu): void
    {
        foreach ($menu->getEntries() as $pos => $item) {
            if (!$this->hasPermission($item)) {
                $menu->removeItemAt($pos);
                continue;
            }
            if ($item instanceof Menu) {
                $this->filterMenuByPermissions($item);
            }
        }
    }

    private function hasPermission(Menu|MenuItem $item): bool
    {
        $permission = $item->getPermission();
        return $permission === null || $this->security->isGranted($permission);
    }
}
