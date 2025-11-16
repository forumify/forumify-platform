<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;
use Forumify\Core\Exception\UserAlreadyExistsException;
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

    /**
     * @throws UserAlreadyExistsException
     */
    public function createUser(NewUser $newUser, bool $requireEmailVerification = true): User
    {
        $this->ensureUsernameAvailable($newUser);

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

    /**
     * @throws UserAlreadyExistsException
     */
    public function createAdmin(NewUser $newUser): User
    {
        $user = $this->createUser($newUser, false);

        /** @var Role $adminRole */
        $adminRole = $this->roleRepository->findOneBy(['slug' => 'super-admin']);
        $user->setRoleEntities([$adminRole]);

        $this->userRepository->save($user);
        return $user;
    }

    /**
     * @throws UserAlreadyExistsException
     */
    private function ensureUsernameAvailable(NewUser $newUser): void
    {
        $query = $this->userRepository
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.username = :username')
            ->orWhere('u.email = :email')
            ->setParameters([
                'username' => $newUser->getUsername(),
                'email' => $newUser->getEmail(),
            ])
            ->getQuery();

        try {
            $existingUserCount = $query->getSingleScalarResult();
        } catch (NonUniqueResultException|NoResultException) {
            $existingUserCount = 0;
        }

        if ($existingUserCount) {
            throw new UserAlreadyExistsException();
        }
    }
}
