<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Super admins can do EVERYTHING on ALL ENTITIES.
 * Only the website owner/administrator should need this role.
 *
 * @extends Voter<string, mixed>
 */
class SuperAdminVoter extends Voter
{
    /** @var array<int, bool> */
    private array $memo = [];

    /** @inheritDoc */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    /** @inheritDoc */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $userId = $user->getId();
        $this->memo[$userId] ??= $this->isSuperAdmin($user);
        return $this->memo[$userId];
    }

    private function isSuperAdmin(User $user): bool
    {
        foreach ($user->getRoleEntities() as $role) {
            if ($role->getRoleName() === 'ROLE_SUPER_ADMIN') {
                return true;
            }
        }
        return false;
    }
}
