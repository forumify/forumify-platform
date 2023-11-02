<?php
declare(strict_types=1);

namespace Forumify\Core\MenuBuilder;

interface MenuBuilderInterface
{
    public function build(Menu $menu): void;
}
