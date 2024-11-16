<?php

declare(strict_types=1);

namespace Forumify\OAuth\GrantType;

use DateInterval;
use DateTime;
use Forumify\Core\Service\TokenService;
use Forumify\OAuth\Entity\OAuthClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ClientCredentials extends AbstractController implements GrantTypeInterface
{
    public function __construct(private readonly TokenService $tokenService)
    {
    }

    public function getGrantType(): string
    {
        return 'client_credentials';
    }

    public function respondToRequest(Request $request, OAuthClient $client): JsonResponse
    {
        $now = new DateTime();
        $expireAt = (clone $now)->add(new DateInterval('PT1H'));
        $expireInSeconds = $expireAt->getTimestamp() - $now->getTimestamp();
        $accessToken = $this->tokenService->createJwt($client, $expireAt);

        return $this->json([
            'token_type' => 'bearer',
            'access_token' => $accessToken,
            'expires_in' => $expireInSeconds,
        ], headers: ['cache-control' => 'no-store']);
    }
}
