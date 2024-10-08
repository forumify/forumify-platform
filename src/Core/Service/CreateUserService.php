<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\DTO\NewUser;
use Forumify\Core\Repository\RoleRepository;
use Forumify\Core\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AccountService $accountService,
    ) {
    }

    public function createUser(NewUser $newUser, bool $requireEmailVerification = true): User
    {
        $user = new User();
        $user->setUsername($newUser->getUsername());
        $user->setDisplayName($newUser->getUsername());
        $user->setEmail($newUser->getEmail());
        $user->setPassword($this->passwordHasher->hashPassword($user, $newUser->getPassword()));
        $user->setTimezone($newUser->getTimezone());
        $user->setEmailVerified(!$requireEmailVerification);
        $this->userRepository->save($user);

        if ($requireEmailVerification) {
            $this->accountService->sendVerificationEmail($user);
        }

        return $user;
    }

    public function createAdmin(NewUser $newUser): User
    {
        $user = $this->createUser($newUser, false);

        $adminRole = $this->roleRepository->findOneBy(['slug' => 'super-admin']);
        $user->setRoleEntities([$adminRole]);

        $this->userRepository->save($user);
        return $user;
    }
}
