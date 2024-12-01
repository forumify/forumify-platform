<?php

declare(strict_types=1);

namespace Forumify\OAuth\GrantType;

use DateInterval;
use DateTime;
use Forumify\Core\Service\TokenService;
use Forumify\OAuth\Entity\OAuthClient;
use Forumify\OAuth\Repository\OAuthAuthorizationCodeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationCode extends AbstractController implements GrantTypeInterface
{
    public function __construct(
        private readonly OAuthAuthorizationCodeRepository $authorizationCodeRepository,
        private readonly TokenService $tokenService,
    ) {
    }

    public function getGrantType(): string
    {
        return 'authorization_code';
    }

    public function respondToRequest(Request $request, OAuthClient $client): JsonResponse
    {
        $authCode = $this->authorizationCodeRepository->findOneBy([
            'client' => $client,
            'code' => $request->request->get('code'),
        ]);

        $now = new DateTime();
        if ($authCode === null || $authCode->getValidUntil() < $now) {
            return $this->json([
                'error' => 'access_denied',
                'error_description' => 'The authorization code is not valid or expired.',
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-5',
            ]);
        }

        $redirectUri = $request->request->get('redirect_uri');
        if ($authCode->getRedirectUri() !== $redirectUri) {
            return $this->json([
                'error' => 'access_denied',
                'error_description' => 'The authorization code was created for a different redirect uri.',
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-5',
            ]);
        }

        $this->authorizationCodeRepository->remove($authCode);

        $scope = trim($authCode->getScope());
        if (!empty($scope)) {
            /** @var string $scope */
            $scope = preg_replace('/\s+/', ' ', $scope);
            $scope = explode(' ', $scope);
        } else {
            $scope = [];
        }

        $now = new DateTime();
        $expireAt = (clone $now)->add(new DateInterval('PT1H'));
        $expireInSeconds = $expireAt->getTimestamp() - $now->getTimestamp();
        $accessToken = $this->tokenService->createJwt($authCode->getUser(), $expireAt, $scope);
        $refreshToken = $this->tokenService->createJwt($authCode->getUser(), (new DateTime())->add(new DateInterval('P1M')));

        return $this->json([
            'token_type' => 'bearer',
            'access_token' => $accessToken,
            'expires_in' => $expireInSeconds,
            'refresh_token' => $refreshToken,
        ], headers: ['cache-control' => 'no-store']);
    }
}
