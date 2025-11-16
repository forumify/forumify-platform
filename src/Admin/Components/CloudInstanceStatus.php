<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Throwable;

#[AsLiveComponent('Forumify\\CloudInstanceStatus', '@Forumify/admin/components/cloud_instance_status.html.twig')]
class CloudInstanceStatus
{
    use DefaultActionTrait;

    public function __construct(
        #[Autowire(env: 'FORUMIFY_URL')]
        private readonly string $forumifyUrl,
        #[Autowire(env: 'APP_SECRET')]
        private readonly string $secret,
        #[Autowire(env: 'DEFAULT_URI')]
        private readonly string $selfUrl,
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getStatus(): ?array
    {
        // return $this->cache->get('forumify.cloud_instance.status', function (ItemInterface $item): ?array {
        //    $item->expiresAfter(new DateInterval('P1D'));
            return $this->getSubscriptionStatus();
        // });
    }

    #[LiveAction]
    public function refresh(): void
    {
        $this->cache->delete('forumify.cloud_instance.status');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getSubscriptionStatus(): ?array
    {
        try {
            return $this->httpClient
                ->request('GET', "{$this->forumifyUrl}/api/hosting/status", [
                    'auth_basic' => [$this->selfUrl, $this->secret],
                ])
                ->toArray();
        } catch (Throwable) {
            return null;
        }
    }
}
