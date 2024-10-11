<?php

declare(strict_types=1);

namespace Forumify\OAuth\Controller;

use DateInterval;
use DateTime;
use Forumify\Core\Service\TokenService;
use Forumify\OAuth\Repository\OAuthAuthorizationCodeRepository;
use Forumify\OAuth\Repository\OAuthClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/token', 'token')]
class TokenController extends AbstractController
{
    public function __invoke(
        Request $request,
        OAuthClientRepository $clientRepository,
        OAuthAuthorizationCodeRepository $authorizationCodeRepository,
        TokenService $tokenService,
    ): JsonResponse {
        $params = $this->getParams([
            'grant_type',
            'code',
            'client_id',
            'client_secret',
            'redirect_uri',
        ], $request->request);

        if (!in_array($params['grant_type'], ['authorization_code', 'client_credentials'], true)) {
            return $this->json([
                'error' => 'unsupported_grant_type',
                'error_description' => "\"{$params['grant_type']}\" is not a supported grant_type.",
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-5',
            ]);
        }

        $client = $clientRepository->findOneBy([
            'clientId' => $params['client_id'],
            'clientSecret' => $params['client_secret'],
        ]);

        if ($client === null) {
            return $this->json([
                'error' => 'invalid_client',
                'error_description' => "Unable to find a client matching the provided client credentials.",
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-5',
            ]);
        }

        $authCode = $authorizationCodeRepository->findOneBy([
            'client' => $client,
            'code' => $params['code'],
        ]);

        $now = new DateTime();
        if ($authCode === null || $authCode->getValidUntil() < $now) {
            return $this->json([
                'error' => 'access_denied',
                'error_description' => 'The authorization code is not valid or expired.',
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-5',
            ]);
        }

        if ($authCode->getRedirectUri() !== null && $authCode->getRedirectUri() !== $params['redirect_uri']) {
            return $this->json([
                'error' => 'access_denied',
                'error_description' => 'The authorization code was created for a different redirect uri.',
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-5',
            ]);
        }

        $authorizationCodeRepository->remove($authCode);

        $scope = trim($authCode->getScope()) ?: [];
        if (!empty($scope)) {
            $scope = preg_replace('/\s+/', ' ', $scope);
            $scope = explode(' ', $scope);
        }

        $now = new DateTime();
        $expireAt = (clone $now)->add(new DateInterval('PT1H'));
        $expireInSeconds = $expireAt->getTimestamp() - $now->getTimestamp();
        $accessToken = $tokenService->createJwt($authCode->getUser(), $expireAt, $scope);
        $refreshToken = $tokenService->createJwt($authCode->getUser(), (new DateTime())->add(new DateInterval('P1M')));

        return $this->json([
            'token_type' => 'bearer',
            'access_token' => $accessToken,
            'expires_in' => $expireInSeconds,
            'refresh_token' => $refreshToken,
        ], headers: ['cache-control' => 'no-store']);
    }

    private function getParams(array $fields, InputBag $bag): array
    {
        $params = [];
        foreach ($fields as $field) {
            $params[$field] = $bag->get($field);
        }
        return $params;
    }
}
