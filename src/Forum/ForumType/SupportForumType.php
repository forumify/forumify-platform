<?php

declare(strict_types=1);

namespace Forumify\Forum\ForumType;

class SupportForumType implements ForumTypeInterface
{
    public static function getType(): string
    {
        return 'support';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/forum/list.html.twig';
    }
}
