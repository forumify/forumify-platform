<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\AccessControlledEntityInterface;
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
     * @param array{
     *     'entity': AccessControlledEntityInterface,
     *     'permission': string
     * } $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $this->validateSubject($subject);
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

    private function validateSubject(array $subject): void
    {
        if (!isset($subject['permission'], $subject['entity'])) {
            throw new RuntimeException('You must supply an entity and permission to use ACL voter');
        }

        if (!$subject['entity'] instanceof AccessControlledEntityInterface) {
            throw new RuntimeException('To use ACL voter the entity must implement ' . AccessControlledEntityInterface::class);
        }
    }

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
