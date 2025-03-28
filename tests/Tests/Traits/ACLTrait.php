<?php
declare(strict_types=1);

namespace Tests\Tests\Traits;

use Forumify\Core\Entity\ACL;
use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\ACLRepository;
use Forumify\Core\Repository\RoleRepository;

trait ACLTrait
{
    use RequiresContainerTrait;

    private function getUserRole(): Role
    {
        $roleRepository = self::getContainer()->get(RoleRepository::class);
        $userRole = $roleRepository->findOneBy(['slug' => 'user']);
        if ($userRole === null) {
            $userRole = new Role();
            $userRole->setTitle('User');
            $roleRepository->save($userRole);
        }
        return $userRole;
    }

    private function getGuestRole(): Role
    {
        $roleRepository = self::getContainer()->get(RoleRepository::class);
        $guestRole = $roleRepository->findOneBy(['slug' => 'guest']);
        if ($guestRole === null) {
            $guestRole = new Role();
            $guestRole->setTitle('Guest');
            $roleRepository->save($guestRole);
        }
        return $guestRole;
    }

    private function createACL(string $entity, mixed $entityId, string $permission, array $roles = []): ACL
    {
        if (empty($roles)) {
            $roles = [$this->getUserRole()];
        }

        $acl = new ACL();
        $acl->setEntity($entity);
        $acl->setEntityId((string)$entityId);
        $acl->setPermission($permission);
        $acl->setRoles($roles);

        self::getContainer()->get(ACLRepository::class)->save($acl);
        return $acl;
    }
}
