<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenService
{
    public function __construct(
        #[\SensitiveParameter]
        private readonly string $appSecret
    ) {
    }

    public function createJwt(UserInterface $user, DateTime $expiresAt, array $resourceAccess = []): string
    {
        return JWT::encode([
            'exp' => $expiresAt->getTimestamp(),
            'sub' => $user->getUserIdentifier(),
            'resource_access' => $resourceAccess,
        ], $this->appSecret, 'HS256');
    }

    public function decodeToken(string $token): array
    {
        return (array)JWT::decode($token, new Key($this->appSecret, 'HS256'));
    }
}