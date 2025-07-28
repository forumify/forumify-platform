<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\AuthorizableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, mixed>
 */
class PermissionVoter extends Voter
{
    /** @var array<string>|null */
    private ?array $permissions = null;

    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var AuthorizableInterface|null $user */
        $user = $token->getUser();
        if ($user === null) {
            return false;
        }

        $permissions = $this->getPermissions($user);
        return in_array($attribute, $permissions, true);
    }

    /**
     * @return array<string>
     */
    private function getPermissions(AuthorizableInterface $user): array
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }

        $permissions = [];
        foreach ($user->getRoleEntities() as $role) {
            foreach ($role->getPermissions() as $permission) {
                $permissions[] = $permission;
            }
        }

        $this->permissions = array_unique($permissions);
        return $this->permissions;
    }
}
