<?php

declare(strict_types=1);

namespace Forumify\OAuth\Security;

use Forumify\OAuth\Entity\IdentityProvider;
use Forumify\OAuth\Idp\IdentityProviderInterface;
use Forumify\OAuth\Repository\IdentityProviderRepository;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

#[AutoconfigureTag('forumify.authenticator')]
class IdentityProviderAuthenticator extends AbstractAuthenticator
{
    /**
     * @param iterable<IdentityProviderInterface> $idpTypes
     */
    public function __construct(
        #[AutowireIterator('forumify.oauth.identity_provider', defaultIndexMethod: 'getType')]
        private readonly iterable $idpTypes,
        private readonly IdentityProviderRepository $identityProviderRepository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        $route = $request->attributes->get('_route');
        if ($route !== 'forumify_oauth_idp_callback') {
            return false;
        }

        return $request->attributes->has('idp');
    }

    public function authenticate(Request $request): Passport
    {
        $idpSlug = $request->attributes->get('idp')['slug'] ?? null;
        if (empty($idpSlug)) {
            throw new BadCredentialsException('No idp slug provided in request');
        }

        /** @var IdentityProvider|null $idp */
        $idp = $this->identityProviderRepository->findOneBy(['slug' => $idpSlug]);
        if ($idp === null) {
            throw new ProviderNotFoundException("Provider with slug $idpSlug does not exist.");
        }

        /** @var array<string, IdentityProviderInterface> $idpTypes */
        $idpTypes = iterator_to_array($this->idpTypes);
        $idpType = $idpTypes[$idp->getType()] ?? null;
        if ($idpType === null) {
            throw new ProviderNotFoundException("No provider of type {$idp->getType()} exists.");
        }

        $user = $idpType->callback($idp);
        if ($user === null) {
            throw new BadCredentialsException('Unable to get user information from identity provider.');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), fn () => $user),
            [new RememberMeBadge()]
        );
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
