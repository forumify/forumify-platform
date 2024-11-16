<?php

declare(strict_types=1);

namespace Forumify\Admin\Service;

use Forumify\Admin\Exception\MarketplaceNotConnectedException;
use Forumify\Admin\Exception\MarketplaceTokenException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

class MarketplaceService
{
    private ?Client $client = null;

    public function __construct(
        private readonly MarketplaceConnectService $connectService
    ) {
    }

    /**
     * @throws MarketplaceNotConnectedException
     */
    public function getCustomer(): ?array
    {
        try {
            $response = $this->getClient()->get('/api/marketplace/customers/self')->getBody()->getContents();
            $customer = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException|GuzzleException|MarketplaceTokenException) {
            return null;
        }

        return $customer;
    }

    /**
     * @throws MarketplaceNotConnectedException|MarketplaceTokenException
     */
    private function getClient(): Client
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $this->client = $this->connectService->getAuthenticatedClient();
        return $this->client;
    }
}
