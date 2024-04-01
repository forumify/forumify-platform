<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACL;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ACLRepository;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccessControlListVoter extends Voter
{
    public function __construct(private readonly ACLRepository $aclRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === VoterAttribute::ACL->value
            && isset($subject['permission'], $subject['entity'])
            && $subject['entity'] instanceof AccessControlledEntityInterface
            && is_string($subject['permission']);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        ['permission' => $permission, 'entity' => $entity] = $subject;
        $default = $subject['default'] ?? false;

        $acl = $this->aclRepository->findOneByEntityAndPermission($entity, $permission);
        if ($acl === null) {
            return $default;
        }

        /** @var User|null $user */
        $user = $token->getUser();
        if ($user === null) {
            return $this->isGuestAllowed($acl);
        }

        $userRoles = $user->getRoles();
        foreach ($acl->getRoles() as $role) {
            if (in_array($role->getRoleName(), $userRoles, true)) {
                return true;
            }
        }
        return false;
    }

    private function isGuestAllowed(ACL $acl): bool
    {
        foreach ($acl->getRoles() as $role) {
            if ($role->getRoleName() === 'ROLE_GUEST') {
                return true;
            }
        }
        return false;
    }
}
