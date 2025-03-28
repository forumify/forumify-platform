<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class OnlineUserWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'forum.online_users';
    }

    public function getCategory(): string
    {
        return 'forum';
    }

    public function getPreview(): string
    {
        return $this->render('@Forumify/frontend/cms/widgets/online_users.html.twig');
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/online_users.html.twig';
    }
}
