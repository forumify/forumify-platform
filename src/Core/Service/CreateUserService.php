<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\NewUser;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateUserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EmailVerificationService $emailVerificationService,
    ) {
    }

    public function createUser(NewUser $newUser): User
    {
        $user = new User();
        $user->setUsername($newUser->getUsername());
        $user->setEmail($newUser->getEmail());
        $user->setPassword($this->passwordHasher->hashPassword($user, $newUser->getPassword()));
        $this->userRepository->save($user);

        $this->emailVerificationService->sendEmailVerificationEmail($user);

        return $user;
    }
}
