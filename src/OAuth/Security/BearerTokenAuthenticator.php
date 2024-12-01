<?php

declare(strict_types=1);

namespace Forumify\OAuth\Security;

use Exception;
use Forumify\Core\Service\TokenService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

#[AutoconfigureTag('forumify.authenticator')]
class BearerTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(private readonly TokenService $tokenService)
    {
    }

    public function supports(Request $request): ?bool
    {
        $accessToken = $request->headers->get('Authorization');
        return $accessToken !== null && str_starts_with($accessToken, 'Bearer');
    }

    public function authenticate(Request $request): Passport
    {
        $header = $request->headers->get('Authorization');
        if ($header === null) {
            throw new TokenNotFoundException();
        }

        $token = substr($header, strpos($header, ' ') + 1);
        try {
            $decoded = $this->tokenService->decodeToken($token);
        } catch (Exception $ex) {
            throw new BadCredentialsException(previous: $ex);
        }

        return new SelfValidatingPassport(new UserBadge($decoded['sub']));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
