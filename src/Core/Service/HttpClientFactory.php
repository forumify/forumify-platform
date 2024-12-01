<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class HttpClientFactory
{
    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $env
    ) {
    }

    /**
     * @see Client::__construct
     *
     * @param array<string, mixed> $config
     */
    public function getClient(array $config = []): Client
    {
        return new Client(array_merge([
            'verify' => $this->env === 'prod',
        ], $config));
    }
}
