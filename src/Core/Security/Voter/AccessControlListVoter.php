<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACL;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ACLRepository;
use Forumify\Core\Security\VoterAttribute;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccessControlListVoter extends Voter
{
    private array $aclMemo = [];

    public function __construct(private readonly ACLRepository $aclRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === VoterAttribute::ACL->value && is_array($subject);
    }

    /**
     * @param array $subject
     *      [
     *          'entity' => AccessControlledEntityInterface,
     *          'permission' => string,
     *          'always_block_guest' => bool
     *      ]
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $this->validateSubject($subject);
        /** @var AccessControlledEntityInterface $entity */
        ['permission' => $permission, 'entity' => $entity] = $subject;

        /** @var User|null $user */
        $user = $token->getUser();
        $memoKey = $this->getMemoKey($entity, $permission, $user);
        if (isset($this->aclMemo[$memoKey])) {
            return $this->aclMemo[$memoKey];
        }

        $acl = $this->aclRepository->findOneByEntityAndPermission($entity, $permission);
        if ($acl === null) {
            return false;
        }

        $result = $this->voteOnACL($user, $acl);
        $this->aclMemo[$memoKey] = $result;

        return $result;
    }

    private function voteOnACL(?User $user, ACL $acl): bool
    {
        if ($user === null) {
            if ($subject['always_block_guest'] ?? false) {
                return false;
            }
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

    private function validateSubject(array $subject): void
    {
        if (!isset($subject['permission'], $subject['entity'])) {
            throw new RuntimeException('You must supply an entity and permission to use ACL voter');
        }

        if (!$subject['entity'] instanceof AccessControlledEntityInterface) {
            throw new RuntimeException('To use ACL voter the entity must implement ' . AccessControlledEntityInterface::class);
        }
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

    private function getMemoKey(AccessControlledEntityInterface $entity, string $permission, ?User $user): string
    {
        $userIdentifier = $user?->getUserIdentifier() ?? 'GUEST';

        $aclParameters = $entity->getACLParameters();
        return $userIdentifier . '-' . $aclParameters->entity . '(' . $aclParameters->entityId . ')' . '::' . $permission;
    }
}
