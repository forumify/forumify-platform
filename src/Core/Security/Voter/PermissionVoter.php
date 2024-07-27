<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return is_string($attribute);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if ($user === null) {
            return false;
        }

        // Flatten and get unique permissions
        $permissions = $this->getPermissions($user);

        // Check if the attribute is in the user's permissions
        return in_array($attribute, $permissions, true);
    }

    private function getPermissions(User $user): array
    {
        $permissions = [];

        // Iterate through each role of the user and collect permissions
        foreach ($user->getRoleEntities() as $role) {
            $permissions = array_merge($permissions, $role->getPermissions());
        }

        return array_unique($permissions);
    }
}
