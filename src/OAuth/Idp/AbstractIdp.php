<?php

declare(strict_types=1);

namespace Forumify\OAuth\Idp;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\DTO\NewUser;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Service\CreateUserService;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractIdp implements IdentityProviderInterface
{
    private UserRepository $userRepository;
    private CreateUserService $createUserService;

    public function getOrCreateUser(string $email, string $username): User
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user !== null) {
            return $user;
        }

        $username = $this->findAvailableUsername($username);
        $newUser = new NewUser();
        $newUser->setUsername($username);
        $newUser->setEmail($email);
        $newUser->setPassword(bin2hex(random_bytes(24)));
        return $this->createUserService->createUser($newUser);
    }

    private function findAvailableUsername(string $preferredUsername): string
    {
        $i = 0;
        $username = $preferredUsername;
        do {
            if ($i > 0) {
                $username = $preferredUsername . $i;
            }

            $foundUser = $this->userRepository->findOneBy(['username' => $username]);
            $i++;
        } while ($foundUser !== null);

        return $username;
    }

    #[Required]
    public function setUserRepository(UserRepository $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    #[Required]
    public function setCreateUserService(CreateUserService $createUserService): void
    {
        $this->createUserService = $createUserService;
    }
}
