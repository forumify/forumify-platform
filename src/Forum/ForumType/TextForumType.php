<?php

declare(strict_types=1);

namespace Forumify\Forum\ForumType;

class TextForumType implements ForumTypeInterface
{
    public static function getType(): string
    {
        return 'text';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/forum/list.html.twig';
    }
}
