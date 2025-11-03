<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @extends TotalGraph<User>
 */
#[AsTwigComponent('Forumify\\Admin\\UserGraph', '@Forumify/admin/dashboard/components/tile.html.twig')]
class UserGraph extends TotalGraph
{
    public function __construct(
        UserRepository $userRepository,
    ) {
        parent::__construct($userRepository);
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.users';
    }

    public function getIcon(): string
    {
        return 'ph-users-three';
    }

    public function getGraphHeight(): int
    {
        return 200;
    }
}
