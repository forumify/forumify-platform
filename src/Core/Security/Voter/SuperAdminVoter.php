<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

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
    /** @inheritDoc */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    /** @inheritDoc */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $token->getRoleNames(), true);
    }
}
