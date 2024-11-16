<?php

declare(strict_types=1);

namespace Forumify\OAuth\Controller;

use DateInterval;
use DateTime;
use Forumify\Core\Service\TokenService;
use Forumify\OAuth\GrantType\GrantTypeInterface;
use Forumify\OAuth\Repository\OAuthAuthorizationCodeRepository;
use Forumify\OAuth\Repository\OAuthClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        OAuthAuthorizationCodeRepository $authorizationCodeRepository,
        TokenService $tokenService,
        #[AutowireIterator('forumify.oauth.grant_type')]
        iterable $grantTypes,
    ): JsonResponse {
        $params = $this->getParams([
            'grant_type',
            'client_id',
            'client_secret',
        ], $request->request);

        $authHeader = $request->headers->get('Authorization');
        if ($authHeader !== null && str_starts_with($authHeader, 'Basic')) {
            $basicToken = substr($authHeader, strpos($authHeader, ' '));
            $basicAuth = explode(':', base64_decode($basicToken));
            if (count($basicAuth) === 2) {
                $params['client_id'] = $basicAuth[0];
                $params['client_secret'] = $basicAuth[1];
            }
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

        /** @var GrantTypeInterface|null $grantType */
        $grantType = null;
        foreach ($grantTypes as $type) {
            if ($type->getGrantType() === $params['grant_type']) {
                $grantType = $type;
                break;
            }
        }

        if ($grantType === null) {
            return $this->json([
                'error' => 'unsupported_grant_type',
                'error_description' => "\"{$params['grant_type']}\" is not a supported grant_type.",
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-5',
            ]);
        }

        return $grantType->respondToRequest($request, $client);
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
