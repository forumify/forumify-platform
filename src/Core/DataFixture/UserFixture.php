<?php

declare(strict_types=1);

namespace Forumify\Core\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Forumify\Core\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements DependentFixtureInterface
{
    public const ADMIN_REFERENCE = 'user.admin';
    public const USER_REFERENCE = 'user.user';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->createUser('admin', 'admin@forumify.net', 'test123', [
            RoleFixture::SUPER_ADMIN_REFERENCE,
        ]);
        $manager->persist($admin);

        $user = $this->createUser('user', 'user@forumify.net', 'test123');
        $manager->persist($user);

        $user = $this->createUser('user1', 'user1@forumify.net', 'test123');
        $manager->persist($user);

        $user = $this->createUser('user2', 'user2@forumify.net', 'test123');
        $manager->persist($user);

        $user = $this->createUser('user3', 'user3@forumify.net', 'test123');
        $manager->persist($user);

        $user = $this->createUser('user4', 'user4@forumify.net', 'test123');
        $manager->persist($user);

        $manager->flush();

        $this->addReference(self::ADMIN_REFERENCE, $admin);
        $this->addReference(self::USER_REFERENCE, $user);
    }

    private function createUser(string $username, string $email, string $password, array $roles = []): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setEmailVerified(true);

        $roleCollection = new ArrayCollection();
        foreach ($roles as $role) {
            $roleCollection->add($this->getReference($role));
        }
        $user->setRoleEntities($roleCollection);

        return $user;
    }

    public function getDependencies(): array
    {
        return [
            RoleFixture::class
        ];
    }
}
