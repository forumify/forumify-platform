<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\AuthorizableInterface;
use Forumify\Core\Entity\Role;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Role>
 */
class AssignRoleVoter extends Voter
{
    use LowestUserRoleTrait;

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === VoterAttribute::AssignRole->value && $subject instanceof Role;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof AuthorizableInterface) {
            return false;
        }

        $lowestRolePos = $this->getLowestRolePos($user);
        return $lowestRolePos <= $subject->getPosition();
    }
}
