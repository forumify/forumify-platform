<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, mixed>
 */
class AdminVoter extends Voter
{
    /** @var array<int, bool> */
    private array $memo = [];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'ROLE_ADMIN' || $attribute === VoterAttribute::Administrator->value;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $userId = $user->getId();
        $this->memo[$userId] ??= $this->isAdmin($user);
        return $this->memo[$userId];
    }

    private function isAdmin(User $user): bool
    {
        foreach ($user->getRoleEntities() as $role) {
            if ($role->isAdministrator()) {
                return true;
            }
        }

        return false;
    }
}
