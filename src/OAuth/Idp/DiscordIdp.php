<?php

declare(strict_types=1);

namespace Forumify\OAuth\Idp;

use Forumify\Core\Form\DTO\NewUser;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Service\CreateUserService;
use Forumify\OAuth\Entity\IdentityProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class DiscordIdp extends AbstractOAuthIdp
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly UserRepository $userRepository,
        private readonly CreateUserService $createUserService,
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

    protected function tokenToUser(array $token): ?UserInterface
    {
        $data = $this->getUserDataFromDiscord($token['access_token']);
        if (empty($data)) {
            return null;
        }

        $email = $data['email'];
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user !== null) {
            return $user;
        }

        $username = $this->findAvailableUsername($data['username']);

        $newUser = new NewUser();
        $newUser->setUsername($username);
        $newUser->setEmail($email);
        $newUser->setPassword(bin2hex(random_bytes(24)));
        return $this->createUserService->createUser($newUser);
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

    private function findAvailableUsername(string $preferredUsername): string
    {
        $i = 0;
        $username = $preferredUsername;
        do {
            if ($i > 0) {
                $username = $preferredUsername . $i;
            }

            $foundUser = $this->userRepository->findOneBy(['username' => $username]);
            $i++;
        } while ($foundUser !== null);

        return $username;
    }
}
