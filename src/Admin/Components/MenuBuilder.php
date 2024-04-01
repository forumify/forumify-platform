<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('MenuBuilder', '@Forumify/admin/components/menu_builder/menu_builder.html.twig')]
class MenuBuilder
{
    use DefaultActionTrait;
}
