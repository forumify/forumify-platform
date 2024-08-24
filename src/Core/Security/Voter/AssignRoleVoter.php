<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AssignRoleVoter extends Voter
{
    private array $lowestPosMemo = [];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === VoterAttribute::AssignRole->value && $subject instanceof Role;
    }

    /**
     * @param Role $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $lowestRolePos = $this->getLowestRolePos($user);
        return $lowestRolePos <= $subject->getPosition();
    }

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
