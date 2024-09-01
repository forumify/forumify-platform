<?php

declare(strict_types=1);

namespace Forumify\OAuth\Security;

use Exception;
use Forumify\Core\Service\TokenService;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private readonly TokenService $tokenService)
    {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        try {
            $decoded = $this->tokenService->decodeToken($accessToken);
        } catch (Exception $ex) {
            throw new BadCredentialsException('Access Denied.', previous: $ex);
        }

        return new UserBadge($decoded['sub']);
    }
}
