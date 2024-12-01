<?php

declare(strict_types=1);

namespace Forumify\Admin\MenuBuilder;

use Forumify\Core\MenuBuilder\MenuBuilderInterface;
use Forumify\Core\MenuBuilder\MenuManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class AdminMenuManager extends MenuManager
{
    /**
     * @param iterable<MenuBuilderInterface> $menuBuilders
     */
    public function __construct(
        #[AutowireIterator('forumify.menu_builder.admin')]
        iterable $menuBuilders,
        Security $security,
    ) {
        parent::__construct($menuBuilders, $security);
    }
}
