<?php

declare(strict_types=1);

namespace Forumify\OAuth\Controller;

use Forumify\Core\Entity\User;
use Forumify\OAuth\Entity\OAuthAuthorizationCode;
use Forumify\OAuth\Repository\OAuthAuthorizationCodeRepository;
use Forumify\OAuth\Repository\OAuthClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/authorize', 'authorize')]
class AuthorizeController extends AbstractController
{
    public function __invoke(
        Request $request,
        OAuthClientRepository $clientRepository,
        OAuthAuthorizationCodeRepository $authorizationCodeRepository
    ): Response {
        $params = $this->getParams($request);

        $session = $request->getSession();
        if ($session->has('oauth.authorization_code_params')) {
            $sessionParams = $session->get('oauth.authorization_code_params', '{}');
            $sessionParams = json_decode($sessionParams, true, 512, JSON_THROW_ON_ERROR);
            $params = array_merge($params, $sessionParams);

            // These are a 1 time use only.
            $session->remove('oauth.authorization_code_params');
        }

        if ($params['response_type'] !== 'code') {
            return $this->json([
                'error' => 'unsupported_response_type',
                'error_description' => "\"{$params['response_type']}\" is not a supported response type.",
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-4.1',
            ], Response::HTTP_BAD_REQUEST);
        }

        $client = $clientRepository->findOneBy(['clientId' => $params['client_id']]);
        if ($client === null) {
            return $this->json([
                'error' => 'invalid_request',
                'error_description' => 'Missing required parameter "client_id"',
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-4.1',
            ], Response::HTTP_BAD_REQUEST);
        }

        $redirectUri = $params['redirect_uri'];
        $redirectIsValid = false;
        foreach ($client->getRedirectUris() as $pattern) {
            if (preg_match("/^$pattern$/", $redirectUri)) {
                $redirectIsValid = true;
                break;
            }
        }

        if (!$redirectIsValid) {
            return $this->json([
                'error' => 'access_denied',
                'error_description' => 'The provided redirect_uri is not whitelisted.',
                'error_uri' => 'https://datatracker.ietf.org/doc/html/rfc6749#section-4.1',
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if ($user === null) {
            $session->set('oauth.authorization_code_params', json_encode($params, JSON_THROW_ON_ERROR));
            return $this->redirectToRoute('forumify_core_login', ['_target_path' => '/oauth/authorize']);
        }

        $code = new OAuthAuthorizationCode();
        $code->setCode(uniqid(bin2hex(random_bytes(6))));
        $code->setClient($client);
        $code->setUser($user);
        $code->setScope($params['scope'] ?? '');
        $code->setRedirectUri($redirectUri);

        $authorizationCodeRepository->save($code);

        $responseQueryParams = ['code' => $code->getCode()];
        if ($params['state']) {
            $responseQueryParams['state'] = $params['state'];
        }

        return $this->redirect($redirectUri . '?' . http_build_query($responseQueryParams));
    }

    /**
     * @return array<string, string>
     */
    private function getParams(Request $request): array
    {
        $fields = [
            'response_type',
            'client_id',
            'redirect_uri',
            'scope',
            'state',
        ];

        $params = [];
        foreach ($fields as $field) {
            $params[$field] = (string)$request->query->get($field) ?: null;
        }
        return $params;
    }
}
