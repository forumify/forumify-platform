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
        if (empty($token['access_token'])) {
            return null;
        }

        $data = $this->getUserDataFromDiscord($token['access_token']);
        return $this->getOrCreateUser(
            $idp,
            $data['id'],
            $data['email'],
            $data['username'],
        );
    }

    /**
     * @return array{id: string, email: string, username: string}
     */
    private function getUserDataFromDiscord(string $token): array
    {
        try {
             $data = $this->httpClient
                ->request('GET', 'https://discord.com/api/users/@me', [
                    'auth_bearer' => $token,
                ])
                ->toArray();
        } catch (Throwable $ex) {
            throw new IdentityProviderException('Unable to get Discord user data.', previous: $ex);
        }

        if (!isset($data['id'], $data['email'], $data['username'])) {
            throw new IdentityProviderException('Discord returned malformed user data');
        }

        /** @var array{id: string, email: string, username: string} $data */
        return $data;
    }
}
