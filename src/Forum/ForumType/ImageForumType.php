<?php

declare(strict_types=1);

namespace Forumify\Forum\ForumType;

class ImageForumType implements ForumTypeInterface
{
    public static function getType(): string
    {
        return 'image';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/forum/list.html.twig';
    }
}
