<?php

declare(strict_types=1);

namespace Forumify\Forum\MenuBuilder;

use Forumify\Core\MenuBuilder\Menu;
use Forumify\Core\MenuBuilder\MenuItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForumMenuBuilder implements ForumMenuBuilderInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function build(Menu $menu): void
    {
        $menu->addItem(new MenuItem('Forum', $this->urlGenerator->generate('forumify_forum_forum')));
    }
}
