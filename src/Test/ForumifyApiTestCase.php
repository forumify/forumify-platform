<?php

declare(strict_types=1);

namespace Forumify\Test;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tests\Tests\Factories\OAuth\OAuthClientFactory;
use Tests\Tests\Traits\UserTrait;
use Zenstruck\Foundry\Test\Factories;

abstract class ForumifyApiTestCase extends ApiTestCase
{
    use Factories;
    use UserTrait;

    private ?string $token = null;
    private ?HttpClientInterface $client = null;

    protected function getHttpClient(): HttpClientInterface
    {
        $this->client ??= static::createClient();
        return $this->client;
    }

    protected function request(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        return $this->getHttpClient()->request($method, $endpoint, $this->withDefaultOptions($options));
    }

    protected function get(string $endpoint, array $options = []): array
    {
        return $this->request('GET', $endpoint, $options)->toArray();
    }

    protected function getCollection(string $endpoint, array $options = []): array
    {
        return $this->get($endpoint, $options)['member'];
    }

    protected function post(string $endpoint, array $options = []): array
    {
        return $this->request('POST', $endpoint, $options)->toArray();
    }

    protected function patch(string $endpoint, array $options = []): array
    {
        $options['headers'] = array_merge([
            'Content-Type' => 'application/merge-patch+json',
        ], $options['headers'] ?? []);

        return $this->request('PATCH', $endpoint, $options)->toArray();
    }

    protected function delete(string $endpoint, array $options = []): void
    {
        $this->request('DELETE', $endpoint, $options);
    }

    protected function withDefaultOptions(array $options): array
    {
        if ($this->token === null) {
            $client = OAuthClientFactory::createOne([
                'user' => $this->createAdmin(),
                'clientId' => 'forumify-test-client',
                'clientSecret' => 'not_a_real_secret',
            ]);

            $response = $this->getHttpClient()->request('POST', '/oauth/token', [
                'body' => [
                    'grantt_type' => 'client_credentials',
                    'client_id' => $client->getClientId(),
                    'client_secret' => $client->getClientSecret(),
                ],
            ])->toArray();

            $this->token = $response['access_token'];
        }

        $options['headers'] = array_merge([
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/ld+json',
        ], $options['headers'] ?? []);

        if (empty($options['body']) && empty($options['json'])) {
            $options['body'] = '{}';
        }

        return $options;
    }
}
