<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder\MenuType;

use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\MenuTypeInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Twig\Environment;

class MenuCollectionType extends AbstractMenuType
{
    /**
     * @var array<string, MenuTypeInterface>
     */
    private array $menuTypes;

    public function __construct(
        #[TaggedIterator('forumify.menu_builder.type')]
        iterable $menuTypes,
        private readonly Environment $twig,
    ) {
        foreach ($menuTypes as $menuType) {
            if ($menuType instanceof MenuTypeInterface) {
                $this->menuTypes[$menuType->getType()] = $menuType;
            }
        }
    }

    public function getType(): string
    {
        return 'collection';
    }

    public function render(MenuItem $item): string
    {
        $menuHtml = '';
        foreach ($item->getChildren() as $child) {
            $menuType = $this->menuTypes[$child->getType()] ?? null;
            if ($menuType === null) {
                continue;
            }

            $menuHtml .= $menuType->render($child);
        }

        $template = "<div {{ stimulus_controller('forumify/forumify-platform/menu') }} data-placement='{{ placement }}'>
            <a class='btn-link' {{ stimulus_target('forumify/forumify-platform/menu', 'openButton') }}>
                {{ name }}
            </a>
            <div class='menu' {{ stimulus_target('forumify/forumify-platform/menu', 'menu') }}>
                {{ inner|raw }}
            </div>
        </div>";

        return $this->twig->createTemplate($template)->render([
            'name' => $item->getName(),
            'placement' => $item->getParent() === null ? 'bottom-start' : 'right',
            'inner' => $menuHtml,
        ]);
    }
}
