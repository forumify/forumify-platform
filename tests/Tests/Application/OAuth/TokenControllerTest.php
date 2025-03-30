<?php

declare(strict_types=1);

namespace Tests\Tests\Application\OAuth;

use Forumify\OAuth\Entity\OAuthClient;
use Forumify\OAuth\Repository\OAuthClientRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TokenControllerTest extends WebTestCase
{
    private const CLIENT_ID = 'forumify-test';
    private const CLIENT_SECRET = 'secret-for-test';

    protected function setUp(): void
    {
        self::createClient();

        $oauthClient = new OAuthClient();
        $oauthClient->setClientId(self::CLIENT_ID);
        $oauthClient->setClientSecret(self::CLIENT_SECRET);
        $oauthClient->setRedirectUris(['https?:\/\/localhost\/.+']);
        self::getContainer()->get(OAuthClientRepository::class)->save($oauthClient);
    }

    public function testClientCredentialsFormEncoded(): void
    {
        $client = static::getClient();
        $client->request('POST', '/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
        ]);
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertResponseIsSuccessful();
        self::assertEquals('bearer', $response['token_type']);
        self::assertNotEmpty($response['access_token']);
        self::assertGreaterThan(0, $response['expires_in']);
    }

    public function testClientCredentialsBasic(): void
    {
        $client = static::getClient();
        $client->request('POST', '/oauth/token', [
            'grant_type' => 'client_credentials'
        ], server: [
            'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode(self::CLIENT_ID . ':' . self::CLIENT_SECRET)
        ]);
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertResponseIsSuccessful();
        self::assertEquals('bearer', $response['token_type']);
        self::assertNotEmpty($response['access_token']);
        self::assertGreaterThan(0, $response['expires_in']);
    }

    public function testBadClientCredentials(): void
    {
        $client = static::getClient();
        $client->request('POST', '/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'does-not-exist',
            'client_secret' => 'not-real'
        ]);
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertResponseStatusCodeSame(400);
        self::assertEquals('invalid_client', $response['error']);
    }

    public function testUnsupportedGrant(): void
    {
        $client = static::getClient();
        $client->request('POST', '/oauth/token', [
            'grant_type' => 'not_a_real_grant',
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
        ]);
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertResponseStatusCodeSame(400);
        self::assertEquals('unsupported_grant_type', $response['error']);
    }
}
