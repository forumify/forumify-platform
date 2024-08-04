<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Security\UserInterface;
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
        /** @var UserInterface|null $user */
        $user = $token->getUser();
        if ($user === null) {
            return false;
        }

        return in_array($attribute, $user->getPermissions(), true);
    }
}
