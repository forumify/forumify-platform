<?php

declare(strict_types=1);

namespace Forumify\OAuth\Controller;

use Forumify\OAuth\GrantType\GrantTypeInterface;
use Forumify\OAuth\Repository\OAuthClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/token', 'token')]
class TokenController extends AbstractController
{
    /**
     * @param iterable<GrantTypeInterface> $grantTypes
     */
    public function __invoke(
        Request $request,
        OAuthClientRepository $clientRepository,
        #[AutowireIterator('forumify.oauth.grant_type')]
        iterable $grantTypes,
    ): JsonResponse {
        $clientId = $request->request->get('client_id');
        $clientSecret = $request->request->get('client_secret');

        $authHeader = $request->headers->get('Authorization');
        if ($authHeader !== null && str_starts_with($authHeader, 'Basic')) {
            $basicToken = substr($authHeader, strpos($authHeader, ' '));
            $basicAuth = explode(':', base64_decode($basicToken));
            if (count($basicAuth) === 2) {
                [$clientId, $clientSecret] = $basicAuth;
            }
        }

        $grantType = null;
        $requestedGrantType = $request->request->get('grant_type');
        foreach ($grantTypes as $type) {
            if ($type->getGrantType() === $requestedGrantType) {
                $grantType = $type;
                break;
            }
        }

        if ($grantType === null) {
            return $this->json([
                'error' => 'unsupported_grant_type',
                'error_description' => "\"{$requestedGrantType}\" is not a supported grant_type.",
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-5',
            ], Response::HTTP_BAD_REQUEST);
        }

        $client = $clientRepository->findOneBy([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]);

        if ($client === null) {
            return $this->json([
                'error' => 'invalid_client',
                'error_description' => "Unable to find a client matching the provided client credentials.",
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-5',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $grantType->respondToRequest($request, $client);
    }
}
