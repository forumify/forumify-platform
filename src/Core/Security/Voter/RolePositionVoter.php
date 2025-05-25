<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, User>
 */
class RolePositionVoter extends Voter
{
    use LowestUserRoleTrait;

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof User && in_array($attribute, [
            VoterAttribute::UserBan->value,
            VoterAttribute::UserDelete->value,
        ]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $userLowestRole = $this->getLowestRolePos($user);
        $subjectLowestRole = $this->getLowestRolePos($subject);

        return $userLowestRole <= $subjectLowestRole;
    }
}
