<?php

declare(strict_types=1);

namespace Forumify\Api\Security;

use Forumify\Api\Exception\InvalidGrantException;
use Forumify\Api\Exception\OAuthExceptionInterface;
use Forumify\Api\Security\GrantType\GrantTypeInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RFC6749 compliant oauth2 implementation
 */
class OAuthService
{
    public function __construct(
        #[AutowireIterator('forumify.api.oauth.grant_type')]
        private readonly iterable $grantTypes,
    ) {
    }

    /**
     * @throws OAuthExceptionInterface
     */
    public function respondToTokenRequest(Request $request): Response
    {
        return $this
            ->getGrantForRequest($request)
            ->handle($request);
    }

    /**
     * @throws InvalidGrantException
     */
    private function getGrantForRequest(Request $request): GrantTypeInterface
    {
        $grantType = $request->get('grant_type' ?? null);
        if ($grantType === null) {
            throw new InvalidGrantException('Required parameter \'grant_type\' is missing.');
        }

        $grant = null;
        /** @var GrantTypeInterface $supportedGrantType */
        foreach ($this->grantTypes as $supportedGrantType) {
            if ($supportedGrantType->getType() === $grantType) {
                $grant = $supportedGrantType;
                break;
            }
        }

        if ($grant === null) {
            throw new InvalidGrantException("'$grantType' is not supported by this authorization server.");
        }

        return $grant;
    }
}
