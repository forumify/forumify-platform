<?php

declare(strict_types=1);

namespace Forumify\OAuth\Idp;

use Forumify\OAuth\Entity\IdentityProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class DiscordIdp extends AbstractOAuthIdp
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public static function getType(): string
    {
        return 'discord';
    }

    public function getButtonHtml(IdentityProvider $idp): string
    {
        return "<a
            href='{$this->getIdpInitLink($idp)}'
            class='btn-idp-discord'
        >
            <i class='ph-fill ph-discord-logo'></i>
            {$this->t('login.idp.discord')}
        </a>";
    }

    protected function getAuthorizationCodeUri(): string
    {
        return 'https://discord.com/oauth2/authorize';
    }

    protected function getTokenUri(): string
    {
        return 'https://discord.com/api/oauth2/token';
    }

    protected function getScopes(): array
    {
        return ['identify', 'email'];
    }

    protected function tokenToUser(IdentityProvider $idp, array $token): ?UserInterface
    {
        $data = $this->getUserDataFromDiscord($token['access_token']);
        if (empty($data)) {
            return null;
        }

        dd($data);
        return $this->getOrCreateUser(
            $idp,
            $data['id'],
            $data['email'],
            $data['username'],
        );
    }

    private function getUserDataFromDiscord(string $token): array
    {
        try {
            return $this->httpClient
                ->request('GET', 'https://discord.com/api/users/@me', [
                    'auth_bearer' => $token,
                ])
                ->toArray();
        } catch (Throwable $ex) {
            throw new IdentityProviderException('Unable to get Discord user data.', previous: $ex);
        }
    }
}
