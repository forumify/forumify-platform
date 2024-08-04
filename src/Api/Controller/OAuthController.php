<?php

declare(strict_types=1);

namespace Forumify\Api\Controller;

use Forumify\Api\Exception\OAuthExceptionInterface;
use Forumify\Api\Security\OAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('oauth/', 'oauth_')]
class OAuthController extends AbstractController
{
    #[Route('token', 'token', methods: ['POST'])]
    public function token(OAuthService $oAuthService, Request $request): Response
    {
        try {
            return $oAuthService->respondToTokenRequest($request);
        } catch (OAuthExceptionInterface $ex) {
            $error = ['error' => $ex->getError()];
            if ($ex->getErrorDescription()) {
                $error['error_description'] = $ex->getErrorDescription();
            }

            return $this->json($error, Response::HTTP_BAD_REQUEST);
        }
    }
}
