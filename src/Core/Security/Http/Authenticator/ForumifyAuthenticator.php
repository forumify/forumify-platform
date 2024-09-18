<?php

namespace Forumify\Core\Security\Http\Authenticator;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class ForumifyAuthenticator extends AbstractAuthenticator
{
    private AbstractAuthenticator $selectedAuthenticator;

    public function __construct(
        /**
         * iterable<AbstractAuthenticator>
         */
        #[AutowireIterator('forumify.authenticator')]
        private readonly iterable $authenticators
    ) {
    }

    public function supports(Request $request): ?bool
    {
        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->supports($request)) {
                $this->selectedAuthenticator = $authenticator;

                return true;
            }
        }

        return false;
    }

    public function authenticate(Request $request): Passport
    {
        return $this->selectedAuthenticator->authenticate($request);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->selectedAuthenticator->onAuthenticationSuccess($request, $token, $firewallName);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->selectedAuthenticator->onAuthenticationFailure($request, $exception);
    }
}
