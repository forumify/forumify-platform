<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\DTO\NewUser;
use Forumify\Core\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EmailVerificationService $emailVerificationService,
    ) {
    }

    public function createUser(NewUser $newUser, bool $requireValidation = true): User
    {
        $user = new User();
        $user->setUsername($newUser->getUsername());
        $user->setDisplayName($newUser->getUsername());
        $user->setEmail($newUser->getEmail());
        $user->setPassword($this->passwordHasher->hashPassword($user, $newUser->getPassword()));
        $user->setEmailVerified(!$requireValidation);
        $this->userRepository->save($user);

        if ($requireValidation) {
            $this->emailVerificationService->sendEmailVerificationEmail($user);
        }

        return $user;
    }
}
