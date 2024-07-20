<?php

declare(strict_types=1);

namespace Application\Core;

use DateInterval;
use DateTime;
use Forumify\Core\Entity\User;
use Forumify\Core\Form\DTO\NewUser;
use Forumify\Core\Service\CreateUserService;
use Forumify\Core\Service\TokenService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmailVerificationControllerTest extends WebTestCase
{
    public function testVerifyEmail(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = $this->createTestUser();
        $client->loginUser($user);

        /** @var TokenService $tokenService */
        $tokenService = self::getContainer()->get(TokenService::class);
        $token = $tokenService->createJwt(
            $user,
            (new DateTime())->add(new DateInterval('P1D')),
            ['verify-email']
        );

        $client->request('GET', '/verify-email/' . $token);
        self::assertSelectorTextContains('.alert-success', 'Your email was verified successfully');
    }

    private function createTestUser(): User
    {
        /** @var CreateUserService $createUserService */
        $createUserService = self::getContainer()->get(CreateUserService::class);

        $user = new NewUser();
        $user->setUsername('tester');
        $user->setEmail('tester@example.org');
        $user->setPassword('test12345');

        return $createUserService->createUser($user);
    }
}
