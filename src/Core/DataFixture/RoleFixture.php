<?php

declare(strict_types=1);

namespace Forumify\Core\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Forumify\Core\Entity\Role;

class RoleFixture extends Fixture
{
    public const SUPER_ADMIN_REFERENCE = 'role.super_admin';

    public function load(ObjectManager $manager): void
    {
        $role = new Role();
        $role->setTitle('Super Admin');
        $manager->persist($role);
        $this->addReference(self::SUPER_ADMIN_REFERENCE, $role);

        $manager->flush();
    }
}
