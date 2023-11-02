<?php
declare(strict_types=1);

namespace Forumify\Forum\MenuBuilder;

use Forumify\Core\MenuBuilder\MenuBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.menu_builder.forum')]
interface ForumMenuBuilderInterface extends MenuBuilderInterface
{
}
