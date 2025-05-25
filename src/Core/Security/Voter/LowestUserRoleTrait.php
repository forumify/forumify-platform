<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;

trait LowestUserRoleTrait
{
    /** @var array<int, int> */
    private array $lowestPosMemo = [];

    private function getLowestRolePos(User $user): int
    {
        $id = $user->getId();
        if (isset($this->lowestPosMemo[$id])) {
            return $this->lowestPosMemo[$id];
        }

        $this->lowestPosMemo[$id] = $user->getRoleEntities()
            ->reduce(fn (int $lowest, Role $role) => min($role->getPosition(), $lowest), PHP_INT_MAX);
        return $this->lowestPosMemo[$id];
    }
}
