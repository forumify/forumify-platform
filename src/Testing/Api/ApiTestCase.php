<?php

declare(strict_types=1);

namespace Forumify\Testing\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as ApipApiTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Forumify\Core\Service\TokenService;
use Forumify\OAuth\Entity\OAuthClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Forumify\Testing\Factories\Core\RoleFactory;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Factories\OAuth\OAuthClientFactory;
use Zenstruck\Foundry\Test\Factories;

abstract class ApiTestCase extends ApipApiTestCase
{
    use Factories;

    protected ?OAuthClient $oauthClient = null;
    private ?HttpClientInterface $client = null;
    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $tokenService = self::getContainer()->get(TokenService::class);
        \assert($tokenService instanceof TokenService);

        $this->tokenService = $tokenService;
    }

    protected function getHttpClient(): HttpClientInterface
    {
        $this->client ??= static::createClient();
        return $this->client;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function request(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        return $this->getHttpClient()->request($method, $endpoint, $this->withDefaultOptions($options));
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function get(string $endpoint, array $options = []): array
    {
        return $this->request('GET', $endpoint, $options)->toArray();
    }

    /**
     * @param array<string, mixed> $options
     * @return array<int, mixed>
     */
    protected function getCollection(string $endpoint, array $options = []): array
    {
        return $this->get($endpoint, $options)['member'];
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function post(string $endpoint, array $options = []): array
    {
        return $this->request('POST', $endpoint, $options)->toArray();
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function patch(string $endpoint, array $options = []): array
    {
        $options['headers'] = array_merge([
            'Content-Type' => 'application/merge-patch+json',
        ], $options['headers'] ?? []);

        return $this->request('PATCH', $endpoint, $options)->toArray();
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function delete(string $endpoint, array $options = []): void
    {
        $this->request('DELETE', $endpoint, $options);
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function withDefaultOptions(array $options): array
    {
        $this->oauthClient ??= OAuthClientFactory::createOne([
            'user' => UserFactory::createOne([
                'roleEntities' => new ArrayCollection([RoleFactory::findOrCreate(['slug' => 'super-admin'])]),
            ]),
        ]);

        $token = 'Bearer ' . $this->tokenService->createJwt($this->oauthClient);

        $options['headers'] = array_merge([
            'Authorization' => $token,
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/ld+json',
        ], $options['headers'] ?? []);

        if (empty($options['body']) && empty($options['json'])) {
            $options['body'] = '{}';
        }

        return $options;
    }
}
