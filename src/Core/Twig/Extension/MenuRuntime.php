<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\MenuBuilder\MenuTypeInterface;
use Forumify\Core\Repository\MenuItemRepository;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Twig\Extension\RuntimeExtensionInterface;

class MenuRuntime implements RuntimeExtensionInterface
{
    /**
     * @var array<string, MenuTypeInterface>
     */
    private array $menuTypes;

    public function __construct(
        private readonly MenuItemRepository $menuItemRepository,
        #[TaggedIterator('forumify.menu_builder.type')]
        iterable $menuTypes,
    ) {
        foreach ($menuTypes as $menuType) {
            if  ($menuType instanceof MenuTypeInterface) {
                $this->menuTypes[$menuType->getType()] = $menuType;
            }
        }
    }

    public function buildForumMenu(): string
    {
        $roots = $this->menuItemRepository->getRoots();

        $menuHtml = '';
        foreach ($roots as $root) {
            $menuType = $this->menuTypes[$root->getType()] ?? null;
            if ($menuType === null) {
                continue;
            }

            $menuHtml .= $menuType->buildItem($root);
        }

        return $menuHtml;
    }
}
