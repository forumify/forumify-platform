<?php

declare(strict_types=1);

namespace Forumify\Admin\MenuBuilder;

use Forumify\Core\MenuBuilder\MenuManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminMenuManager extends MenuManager
{
    public function __construct(
        #[TaggedIterator('forumify.menu_builder.admin')]
        iterable $menuBuilders,
        private readonly Security $security,
    ) {
        parent::__construct($menuBuilders, $security);
    }
}
