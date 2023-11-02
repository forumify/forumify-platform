<?php

declare(strict_types=1);

namespace Forumify\Admin\MenuBuilder;

use Forumify\Core\MenuBuilder\MenuManager;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class AdminMenuManager extends MenuManager
{
    public function __construct(
        #[TaggedIterator('forumify.menu_builder.admin')]
        iterable $menuBuilders
    ) {
        parent::__construct($menuBuilders);
    }
}
