<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Api\EventSubscriber;

use Forumify\Api\EventSubscriber\PluginVersionListener;
use Forumify\Plugin\Attribute\PluginVersion;
use Forumify\Plugin\Service\PluginVersionChecker;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class PluginVersionListenerTest extends TestCase
{
    public function testDeniesResourceWhenVersionIsNotInstalled(): void
    {
        $event = $this->createEvent($this->createPremiumResourceClass());

        $this->createListener(false)($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(403, $response->getStatusCode());
        $body = json_decode((string)$response->getContent(), true);
        self::assertStringContainsString('forumify/test-plugin', $body['error']);
    }

    public function testAllowsResourceWhenVersionIsInstalled(): void
    {
        $event = $this->createEvent($this->createPremiumResourceClass());

        $this->createListener(true)($event);

        self::assertNull($event->getResponse());
    }

    public function testIgnoresResourcesWithoutPluginVersion(): void
    {
        $event = $this->createEvent(stdClass::class);

        $this->createListener(false)($event);

        self::assertNull($event->getResponse());
    }

    public function testIgnoresNonApiRequests(): void
    {
        $event = $this->createEvent(null);

        $this->createListener(false)($event);

        self::assertNull($event->getResponse());
    }

    private function createPremiumResourceClass(): string
    {
        $resource = new #[PluginVersion('forumify/test-plugin', 'premium')] class {
        };

        return $resource::class;
    }

    private function createListener(bool $installed): PluginVersionListener
    {
        $checker = $this->createMock(PluginVersionChecker::class);
        $checker->method('isVersionInstalled')->willReturn($installed);

        return new PluginVersionListener($checker);
    }

    private function createEvent(?string $resourceClass): RequestEvent
    {
        $request = new Request();
        if ($resourceClass !== null) {
            $request->attributes->set('_api_resource_class', $resourceClass);
        }

        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }
}
