<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Admin\MenuBuilder\AdminMenuManager;
use Forumify\Forum\MenuBuilder\ForumMenuManager;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class MenuExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly ForumMenuManager $forumMenuManager,
        private readonly AdminMenuManager $adminMenuManager
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'forum_menu' => $this->forumMenuManager->getMenu(),
            'admin_menu' => $this->adminMenuManager->getMenu(),
        ];
    }
}
