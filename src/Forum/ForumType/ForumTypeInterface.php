<?php

declare(strict_types=1);

namespace Forumify\Forum\ForumType;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.forum.type')]
interface ForumTypeInterface
{
    public static function getType(): string;

    public function getTemplate(): string;
}
