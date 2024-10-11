<?php

declare(strict_types=1);

namespace Forumify\Admin\Service;

use Exception;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\HttpClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MarketplaceConnectService
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        #[Autowire('%env(FORUMIFY_URL)%')]
        private readonly string $forumifyUrl,
        private readonly HttpClientFactory $httpClientFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function isConnected(): bool
    {
        $clientId = $this->settingRepository->get('forumify.client_id');
        $clientSecret = $this->settingRepository->get('forumify.client_secret');

        return !empty($clientId) && !empty($clientSecret);
    }

    /**
     * @throws Exception
     */
    public function registerClient(string $authCode, string $host): void
    {
        $httpClient = $this->httpClientFactory->getClient(['base_uri' => $this->forumifyUrl]);

        try {
            $accessTokenResponse = $httpClient
                ->post('/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'code' => $authCode,
                        'client_id' => 'forumify',
                        'client_secret' => 'forumify',
                        'redirect_uri' => $this->urlGenerator->generate('forumify_admin_marketplace_connect_finish', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                ])
                ->getBody()
                ->getContents();

            $tokens = json_decode($accessTokenResponse, true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $ex) {
            throw new RuntimeException('Unable to fetch access token', previous: $ex);
        }

        try {
            $registerClientResponse = $httpClient
                ->post('/marketplace/connect/register-client/' . urlencode($host), [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $tokens['access_token'],
                    ],
                ])
                ->getBody()
                ->getContents();

            $client = json_decode($registerClientResponse, true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException|\JsonException $ex) {
            throw new RuntimeException('Unable to register client', previous: $ex);
        }

        $this->settingRepository->set('forumify.client_id', $client['clientId']);
        $this->settingRepository->set('forumify.client_secret', $client['clientSecret']);
    }

    public function getRedirectUrl(string $state): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => 'forumify',
            'redirect_uri' => $this->urlGenerator->generate('forumify_admin_marketplace_connect_finish', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'scope' => 'marketplace:connect',
            'state' => $state,
        ]);

        return $this->forumifyUrl . '/oauth/authorize?' . $query;
    }
}
