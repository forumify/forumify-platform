<?php
declare(strict_types=1);

namespace Tests\Tests\Traits;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\DTO\NewUser;
use Forumify\Core\Service\CreateUserService;

trait UserTrait
{
    use RequiresContainerTrait;

    private function createUser(
        string $username = 'tester',
        string $email = 'tester@example.org',
        string $password = 'test12345',
        bool $requiresEmailValidation = false,
    ): User {
        $user = new NewUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($password);

        return self::getContainer()
            ->get(CreateUserService::class)
            ->createUser($user, $requiresEmailValidation);
    }
}
