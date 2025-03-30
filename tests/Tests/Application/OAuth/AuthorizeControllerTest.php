<?php

declare(strict_types=1);

namespace Tests\Tests\Application\OAuth;

use Forumify\OAuth\Entity\OAuthClient;
use Forumify\OAuth\Repository\OAuthClientRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Traits\UserTrait;

class AuthorizeControllerTest extends WebTestCase
{
    private const CLIENT_ID = 'forumify-test';
    private const CLIENT_SECRET = 'secret-for-test';

    use UserTrait;

    protected function setUp(): void
    {
        self::createClient();

        $oauthClient = new OAuthClient();
        $oauthClient->setClientId(self::CLIENT_ID);
        $oauthClient->setClientSecret(self::CLIENT_SECRET);
        $oauthClient->setRedirectUris(['https?:\/\/localhost\/']);
        self::getContainer()->get(OAuthClientRepository::class)->save($oauthClient);
    }

    public function testAuthorizationCode(): void
    {
        /** @var KernelBrowser $client */
        $client = static::getClient();

        $user = $this->createAdmin();
        $client->loginUser($user);

        $client->request('POST', '/oauth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => self::CLIENT_ID,
            'redirect_uri' => 'https://localhost/',
            'state' => 'abc123',
        ]));
        $response = $client->getResponse()->headers->get('Location');

        self::assertNotNull($response);

        $params = [];
        parse_str(parse_url($response, PHP_URL_QUERY), $params);

        $code = $params['code'];
        self::assertEquals('abc123', $params['state']);

        $client->request('POST', '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'code' => $code,
            'redirect_uri' => 'https://localhost/',
        ]);
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertResponseIsSuccessful();
        self::assertEquals('bearer', $response['token_type']);
        self::assertGreaterThan(0, $response['expires_in']);
        self::assertNotEmpty($response['access_token']);
        self::assertNotEmpty($response['refresh_token']);

        $client->getCookieJar()->clear();

        $client->request('GET', '/admin/', server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $response['access_token']]);
        self::assertResponseIsSuccessful();
    }

    public function testAuthorizationInvalidResponseType(): void
    {
        /** @var KernelBrowser $client */
        $client = static::getClient();

        $client->request('POST', '/oauth/authorize?' . http_build_query([
            'response_type' => 'not-a-real-type',
            'client_id' => self::CLIENT_ID,
            'redirect_uri' => 'https://localhost/',
            'state' => 'abc123',
        ]));
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertResponseStatusCodeSame(400);
        self::assertEquals('unsupported_response_type', $response['error']);
    }

    public function testAuthorizationInvalidClient(): void
    {
        /** @var KernelBrowser $client */
        $client = static::getClient();

        $client->request('POST', '/oauth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => 'not-a-real-client',
            'redirect_uri' => 'https://localhost/',
            'state' => 'abc123',
        ]));
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertResponseStatusCodeSame(400);
        self::assertEquals('invalid_request', $response['error']);
    }

    public function testAuthorizationInvalidRedirectUri(): void
    {
        /** @var KernelBrowser $client */
        $client = static::getClient();

        $client->request('POST', '/oauth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => self::CLIENT_ID,
            'redirect_uri' => 'https://somewhere-else/',
            'state' => 'abc123',
        ]));
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertResponseStatusCodeSame(400);
        self::assertEquals('access_denied', $response['error']);
    }
}
