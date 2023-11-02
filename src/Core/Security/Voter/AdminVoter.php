<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'ROLE_ADMIN' || $attribute === VoterAttribute::Administrator->value;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if ($user === null) {
            return false;
        }

        foreach ($user->getRoleEntities() as $role) {
            if ($role->isAdministrator()) {
                return true;
            }
        }

        return false;
    }
}
