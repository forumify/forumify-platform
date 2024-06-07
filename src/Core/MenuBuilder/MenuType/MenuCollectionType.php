<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder\MenuType;

use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\MenuTypeInterface;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Bundle\SecurityBundle\Security;
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
        private readonly Security $security,
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
            $canView = $this->security->isGranted(VoterAttribute::ACL->value, [
                'entity' => $child,
                'permission' => 'view',
            ]);
            if (!$canView) {
                continue;
            }

            $menuType = $this->menuTypes[$child->getType()] ?? null;
            if ($menuType === null) {
                continue;
            }

            $menuHtml .= $menuType->render($child);
        }

        return $this->twig->render('@Forumify/frontend/menu/collection.html.twig', [
            'name' => $item->getName(),
            'placement' => $item->getParent() === null ? 'bottom-start' : 'right',
            'inner' => $menuHtml,
        ]);
    }
}
