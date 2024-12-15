<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use Forumify\Core\Repository\UserRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Forumify\\UserGraph', '@Forumify/admin/dashboard/graphs/registrations.html.twig')]
class UserGraph extends TotalGraph
{
    public function __construct(
        UserRepository $userRepository,
    ) {
        parent::__construct($userRepository);
    }
}
