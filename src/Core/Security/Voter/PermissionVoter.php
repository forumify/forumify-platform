<?php

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Plugin\Entity\Permission;
use Forumify\Core\Repository\PermissionRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
    private PermissionRepository $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return preg_match('/^[a-z]+\.[a-z]+\.[a-z]+\.[a-z]+$/', $attribute) === 1;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $permission = $this->permissionRepository->findOneBy(['permission' => $attribute]);

        if (!$permission) {
            return false;
        }

        foreach ($user->getRoleEntities() as $role) {
            if ($role->getPermissions()->contains($permission)) {
                return true;
            }
        }
        return false;
    }
}