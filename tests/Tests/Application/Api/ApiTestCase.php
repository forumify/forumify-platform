<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as ApipApiTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Forumify\Core\Service\TokenService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tests\Tests\Factories\Core\RoleFactory;
use Tests\Tests\Factories\Core\UserFactory;
use Tests\Tests\Factories\OAuth\OAuthClientFactory;
use Zenstruck\Foundry\Test\Factories;

abstract class ApiTestCase extends ApipApiTestCase
{
    use Factories;

    protected ?string $token = null;
    protected ?HttpClientInterface $client = null;

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
            $oauthClient = OAuthClientFactory::createOne([
                'user' => UserFactory::createOne([
                    'roleEntities' => new ArrayCollection([RoleFactory::findOrCreate(['slug' => 'super-admin'])]),
                ]),
            ]);

            $tokenService = self::getContainer()->get(TokenService::class);
            $this->token = $tokenService->createJwt($oauthClient);
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
