<?php
declare(strict_types=1);

namespace Forumify\Admin\MenuBuilder;

use Forumify\Core\MenuBuilder\MenuBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.menu_builder.admin')]
interface AdminMenuBuilderInterface extends MenuBuilderInterface
{
}
