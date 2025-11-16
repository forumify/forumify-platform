<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ACLRepository;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, array{entity: AccessControlledEntityInterface, permission: string}>
 */
class AccessControlListVoter extends Voter
{
    /** @var array<int|string, array<mixed>> */
    private array $aclMemo = [];

    public function __construct(private readonly ACLRepository $aclRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === VoterAttribute::ACL->value
            && is_array($subject)
            && isset($subject['permission'], $subject['entity'])
            && $subject['entity'] instanceof AccessControlledEntityInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        ['permission' => $permission, 'entity' => $entity] = $subject;

        /** @var User|null $user */
        $user = $token->getUser();
        $userId = (string)($user?->getId() ?? 'guest');
        if (!isset($this->aclMemo[$userId])) {
            $this->aclMemo[$userId] = $this->createACLLookup($user);
        }

        $acl = $entity->getACLParameters();
        return $this->aclMemo[$userId][$acl->entity][$acl->entityId][$permission] ?? false;
    }

    /**
     * @return array<mixed>
     */
    private function createACLLookup(?User $user): array
    {
        $lookup = [];
        $acls = $this->aclRepository->findByUser($user);
        foreach ($acls as $acl) {
            ['entity' => $entity, 'entityId' => $entityId, 'permission' => $permission] = $acl;
            $lookup[$entity][$entityId][$permission] = true;
        }

        return $lookup;
    }
}
