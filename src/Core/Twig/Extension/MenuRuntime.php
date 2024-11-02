<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\MenuBuilder\MenuTypeInterface;
use Forumify\Core\Repository\MenuItemRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Extension\RuntimeExtensionInterface;

class MenuRuntime implements RuntimeExtensionInterface
{
    public const MENU_CACHE_TAG = 'forumify.menu';

    /**
     * @var array<string, MenuTypeInterface>
     */
    private array $menuTypes;

    public function __construct(
        private readonly MenuItemRepository $menuItemRepository,
        private readonly Security $security,
        private readonly CacheInterface $cache,
        #[TaggedIterator('forumify.menu_builder.type')]
        iterable $menuTypes,
    ) {
        foreach ($menuTypes as $menuType) {
            if ($menuType instanceof MenuTypeInterface) {
                $this->menuTypes[$menuType->getType()] = $menuType;
            }
        }
    }

    public static function createMenuCacheKey(UserInterface|null $user): string
    {
        $identifier = $user?->getUserIdentifier() ?? 'guest';
        return "forumify.menu.user_$identifier";
    }

    public function buildForumMenu(): string
    {
        $key = self::createMenuCacheKey($this->security->getUser());
        return $this->cache->get($key, function (ItemInterface $item): string {
            $item->tag(self::MENU_CACHE_TAG);
            return $this->buildMenuHtml();
        });
    }

    private function buildMenuHtml(): string
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
