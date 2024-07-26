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
        $permissions = $this->flattenPermissions($user->getRoleEntities()->toArray());

        return in_array($attribute, $permissions, true);
    }

    private function flattenPermissions(array $roles): array
    {
        $permissions = [];

        foreach ($roles as $role) {
            $permissions = array_merge($permissions, $this->extractPermissions($role->getPermissions()));
        }

        return array_unique($permissions);
    }

    private function extractPermissions(array $permissions, string $prefix = ''): array
    {
        $result = [];

        foreach ($permissions as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->extractPermissions($value, $prefix . $key . '.'));
            } else {
                $result[] = $prefix . $value;
            }
        }
        return $result;
    }
}
