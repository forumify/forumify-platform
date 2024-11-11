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
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if ($user === null) {
            return false;
        }

        $permissions = $this->getPermissions($user);
        return in_array($attribute, $permissions, true);
    }

    private function getPermissions(User $user): array
    {
        $permissions = [];
        foreach ($user->getRoleEntities() as $role) {
            foreach ($role->getPermissions() as $permission) {
                $permissions[] = $permission;
            }
        }

        return array_unique($permissions);
    }
}
