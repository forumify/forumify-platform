<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder;

use Twig\Extension\RuntimeExtensionInterface;

abstract class MenuManager implements RuntimeExtensionInterface
{
    /** @var array<MenuBuilderInterface> */
    protected array $menuBuilders;

    public function __construct(iterable $menuBuilders)
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

        return $menu;
    }
}
