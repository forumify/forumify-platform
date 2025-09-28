<?php

declare(strict_types=1);

namespace Forumify\OAuth\Idp;

use Forumify\Admin\Form\IdentityProvider\DiscordIdpType;
use Forumify\OAuth\Entity\IdentityProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

abstract class AbstractOAuthIdp extends AbstractIdp
{
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;
    private RequestStack $requestStack;
    private HttpClientInterface $httpClient;

    public static function getDataType(): string
    {
        return DiscordIdpType::class;
    }

    public function getIdpInitLink(IdentityProvider $idp): string
    {
        return $this->u('forumify_oauth_idp_init', [
            'slug' => $idp->getSlug(),
        ]);
    }

    public function initLogin(IdentityProvider $idp): Response
    {
        $data = $idp->getData();
        if (!is_array($data) || empty($data['clientId'])) {
            throw new IdentityProviderException("{$idp->getName()} is not configured correctly.");
        }

        $state = bin2hex(random_bytes(16));
        $session = $this->requestStack->getSession();
        $session->set("idp-state__{$idp->getSlug()}", $state);

        $qs = http_build_query([
            'response_type' => 'code',
            'client_id' => $data['clientId'],
            'scope' => implode(' ', $this->getScopes()),
            'state' => $state,
            'redirect_uri' => $this->getRedirectUri($idp),
        ]);
        $codeUri = $this->getAuthorizationCodeUri() . '?' . $qs;

        return new RedirectResponse($codeUri);
    }

    public function callback(IdentityProvider $idp, Request $request): ?UserInterface
    {
        $data = $idp->getData();
        if (!is_array($data) || empty($data['clientId']) || empty($data['clientSecret'])) {
            throw new IdentityProviderException("{$idp->getName()} is not configured correctly.");
        }

        $code = $request->query->get('code');
        if (empty($code)) {
            throw new IdentityProviderException('Identity provider did not return a code.');
        }

        $session = $request->getSession();
        $sessionState = $session->remove("idp-state__{$idp->getSlug()}");
        if (empty($sessionState) || $request->query->get('state') !== $sessionState) {
            throw new IdentityProviderException('State did not match. Please try again.');
        }

        try {
            $token = $this->httpClient
                ->request('POST', $this->getTokenUri(), [
                    'auth_basic' => [$data['clientId'], $data['clientSecret']],
                    'body' => [
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'redirect_uri' => $this->getRedirectUri($idp),
                    ],
                ])
                ->toArray();
        } catch (Throwable $ex) {
            throw new IdentityProviderException('Unable to fetch access token.', previous: $ex);
        }

        return $this->tokenToUser($token);
    }

    private function getRedirectUri(IdentityProvider $idp): string
    {
        return $this->u('forumify_oauth_idp_callback', [
            'slug' => $idp->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    abstract protected function getAuthorizationCodeUri(): string;

    abstract protected function getTokenUri(): string;

    abstract protected function tokenToUser(array $token): ?UserInterface;

    protected function getScopes(): array
    {
        return [];
    }

    protected function u(
        string $pathName,
        array $args = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ): string {
        return $this->urlGenerator->generate($pathName, $args, $referenceType);
    }

    protected function t(string $transKey, array $args = []): string
    {
        return $this->translator->trans($transKey, $args);
    }

    #[Required]
    public function setServices(
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        HttpClientInterface $httpClient,
    ): void {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->httpClient = $httpClient;
    }
}
