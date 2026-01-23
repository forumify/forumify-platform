<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder;

use Forumify\Core\Entity\MenuItem as MenuItemEntity;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\FormTypeInterface;

#[AutoconfigureTag('forumify.menu_builder.type')]
interface MenuTypeInterface
{
    public function getType(): string;

    public function buildItem(MenuItemEntity $item): string;

    /**
     * @return null|class-string<FormTypeInterface<*>>
     */
    public function getPayloadFormType(): ?string;
}
