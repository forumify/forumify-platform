<?php

declare(strict_types=1);

namespace Forumify\Forum\MenuBuilder;

use Forumify\Core\MenuBuilder\MenuManager;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ForumMenuManager extends MenuManager
{
    public function __construct(
        #[TaggedIterator('forumify.menu_builder.forum')]
        iterable $menuBuilders
    ) {
        parent::__construct($menuBuilders);
    }
}
