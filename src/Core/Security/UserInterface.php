<?php
declare(strict_types=1);

namespace Forumify\Core\Security;

use Doctrine\Common\Collections\Collection;
use Forumify\Core\Entity\Role;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserInterface extends SymfonyUserInterface
{
    /**
     * @return Collection<Role>
     */
    public function getRoleEntities(): Collection;

    /**
     * @return array<string>
     */
    public function getPermissions(): array;
}
