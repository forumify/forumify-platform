<?php

declare(strict_types=1);

namespace Forumify\Forum\ForumType;

class MixedForumType implements ForumTypeInterface
{
    public static function getType(): string
    {
        return 'mixed';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/forum/list.html.twig';
    }
}
