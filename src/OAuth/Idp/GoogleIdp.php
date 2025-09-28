<?php

declare(strict_types=1);

namespace Forumify\OAuth\Idp;

use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use Forumify\Admin\Form\IdentityProvider\GoogleIdpType;
use Forumify\OAuth\Entity\IdentityProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class GoogleIdp extends AbstractIdp
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly HttpClientInterface $httpClient,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public static function getType(): string
    {
        return 'google';
    }

    public static function getDataType(): string
    {
        return GoogleIdpType::class;
    }

    public function getButtonHtml(IdentityProvider $idp): string
    {
        $clientId = $idp->getData()['clientId'] ?? '';
        $redirectUri = $this->urlGenerator->generate('forumify_oauth_idp_callback', [
            'slug' => $idp->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return '
            <script src="https://accounts.google.com/gsi/client" async></script>
            <div
                id="g_id_onload"
                data-client_id="' . $clientId . '"
                data-login_uri="' . $redirectUri . '"
                data-auto_prompt="false">
            </div>
            <div class="g_id_signin" style="margin-top: -8px"
                data-type="standard"
                data-size="large"
                data-shape="pill"
                data-theme="outline"
                data-text="sign_in_with"
                data-logo_alignment="center"
                data-width="368">
            </div>
        ';
    }

    public function initLogin(IdentityProvider $idp): Response
    {
        throw new LogicException('Google IDP does not initialize login.');
    }

    public function callback(IdentityProvider $idp, Request $request): ?UserInterface
    {
        $credential = $request->get('credential');
        if (empty($credential)) {
            return null;
        }

        try {
            $decoded = (array)JWT::decode($credential, new CachedKeySet(
                'https://www.googleapis.com/oauth2/v3/certs',
                new Client(),
                new HttpFactory(),
                $this->cache,
            ));
        } catch (Throwable) {
            return null;
        }

        $email = $decoded['email'];
        $username = substr($email, 0, strpos($email, '@'));
        return $this->getOrCreateUser($idp, $email, $email, $username);
    }
}
